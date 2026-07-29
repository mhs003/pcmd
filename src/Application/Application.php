<?php

declare(strict_types=1);

namespace Pcmd\Application;

use Pcmd\CLI\ArgvParser;
use Pcmd\CLI\Output;
use Pcmd\Commands\CacheCommand;
use Pcmd\Commands\ConfigCommand;
use Pcmd\Commands\DoctorCommand;
use Pcmd\Commands\EnvCommand;
use Pcmd\Commands\HelpCommand;
use Pcmd\Commands\ListCommand;
use Pcmd\Commands\VersionCommand;
use Pcmd\Configuration\Config;
use Pcmd\Configuration\ConfigLoader;
use Pcmd\Context\Context;
use Pcmd\Contracts\ContainerInterface;
use Pcmd\Contracts\FilesystemInterface;
use Pcmd\Contracts\LoggerInterface;
use Pcmd\Discovery\CommandDiscovery;
use Pcmd\Discovery\DirectoryScanner;
use Pcmd\Discovery\DiscoveryCache;
use Pcmd\Environment\Detectors\GenericDetector;
use Pcmd\Environment\Detectors\LaravelDetector;
use Pcmd\Environment\Environment;
use Pcmd\Environment\EnvironmentManager;
use Pcmd\Exceptions\ConfigurationException;
use Pcmd\Execution\CommandExecutor;
use Pcmd\Execution\CommandLoader;
use Pcmd\Execution\HookRunner;
use Pcmd\Plugin\PluginManager;
use Pcmd\Filesystem\Filesystem;
use Pcmd\Logging\Logger;
use Pcmd\Process\ProcessManager;
use Pcmd\Registry\CommandMetadata;
use Pcmd\Registry\CommandRegistry;
use Pcmd\Resolution\CommandResolver;
use Pcmd\Resolution\ResolvedCommand;
use Pcmd\Support\Container;
use Pcmd\Support\HelperLoader;
use Pcmd\Terminal\Terminal;

final class Application
{
    private const VERSION = '0.1.0';
    private const NAME = 'pcmd';

    private const RESERVED_COMMANDS = [
        'help', 'list', 'version', 'env', 'doctor',
        'cache:clear', 'cache:rebuild', 'config:show',
        'publish:commands',
    ];

    private ArgvParser $argvParser;
    private Output $output;
    private ContainerInterface $container;
    private PluginManager $pluginManager;

    public function __construct(?ArgvParser $argvParser = null, ?Output $output = null)
    {
        $this->argvParser = $argvParser ?? new ArgvParser();
        $this->output = $output ?? new Output();
        $this->container = new Container();
        $this->pluginManager = new PluginManager();
    }

    /**
     * @param list<string> $argv
     */
    public function run(array $argv = []): int
    {
        $this->argvParser->parse($argv);

        if ($this->argvParser->hasOption('version')) {
            $this->output->line(self::NAME . ' ' . self::VERSION);
            return 0;
        }

        try {
            $config = $this->loadConfig();
            $this->registerServices($config);
            $this->pluginManager->load();
            $environment = $this->detectEnvironment();
            $registry = $this->buildCommandRegistry($environment);
            $resolved = $this->resolveCommand($registry, $environment);

            if ($resolved === null) {
                if ($this->argvParser->hasOption('help') || $this->argvParser->commandName() === '') {
                    $this->printHelp($registry);
                    return 0;
                }

                return 2;
            }

            return $this->executeCommand($resolved, $environment, $config);
        } catch (ConfigurationException $e) {
            $this->output->error('Configuration Error: ' . $e->getMessage());
            return 6;
        } catch (\Throwable $e) {
            $this->output->error('Error: ' . $e->getMessage());
            return 1;
        }
    }

    private function loadConfig(): Config
    {
        $loader = new ConfigLoader();
        return $loader->load();
    }

    private function registerServices(Config $config): void
    {
        $this->container->singleton(Config::class, fn () => $config);

        $this->container->singleton(LoggerInterface::class, function () use ($config) {
            $logDir = $config->string('logging.directory') ?? '/tmp/pcmd';
            $level = $config->string('logging.level') ?? 'warning';
            return new Logger($level, $logDir . '/pcmd.log');
        });

        $this->container->singleton(FilesystemInterface::class, fn () => new Filesystem());
        $this->container->singleton(\Pcmd\Contracts\ProcessInterface::class, fn () => new ProcessManager());
    }

    private function detectEnvironment(): Environment
    {
        $detectors = [
            new LaravelDetector(),
            new GenericDetector(),
        ];

        foreach ($this->pluginManager->detectors() as $pluginDetector) {
            $detectors[] = $pluginDetector;
        }

        $manager = new EnvironmentManager($detectors);

        $cwd = getcwd();

        if ($cwd === false) {
            $cwd = '.';
        }

        return $manager->detect($cwd);
    }

    private function buildCommandRegistry(Environment $environment): CommandRegistry
    {
        $registry = new CommandRegistry();

        $this->registerBuiltinCommands($registry, $environment);

        $discovery = new CommandDiscovery(new DirectoryScanner());
        $discovery->setPluginDirectories($this->pluginManager->commandDirectories());
        $discovered = $discovery->discover($environment);
        $loader = new CommandLoader();

        foreach ($discovered->all() as $metadata) {
            try {
                if (in_array($metadata->name(), self::RESERVED_COMMANDS, true)) {
                    continue;
                }

                $registry->register($metadata);
                $loader->loadMetadata($metadata);
            } catch (\Throwable $e) {
                $this->output->error('Skipped: ' . $metadata->name() . ' - ' . $e->getMessage());
            }
        }

        $this->registerBundledCommands($registry, $environment, $loader);

        return $registry;
    }

    private function registerBuiltinCommands(CommandRegistry $registry, Environment $environment): void
    {
        $help = new CommandMetadata(
            name: 'help',
            file: '',
            description: 'Display help for a command',
            environment: 'generic',
        );
        $registry->register($help);

        $list = new CommandMetadata(
            name: 'list',
            file: '',
            description: 'List available commands',
            environment: 'generic',
        );
        $registry->register($list);

        $version = new CommandMetadata(
            name: 'version',
            file: '',
            description: 'Display version information',
            environment: 'generic',
        );
        $registry->register($version);

        $env = new CommandMetadata(
            name: 'env',
            file: '',
            description: 'Show environment information',
            environment: 'generic',
        );
        $registry->register($env);

        $doctor = new CommandMetadata(
            name: 'doctor',
            file: '',
            description: 'Run system diagnostics',
            environment: 'generic',
        );
        $registry->register($doctor);

        $cacheClear = new CommandMetadata(
            name: 'cache:clear',
            file: '',
            description: 'Clear the discovery cache',
            environment: 'generic',
        );
        $registry->register($cacheClear);

        $cacheRebuild = new CommandMetadata(
            name: 'cache:rebuild',
            file: '',
            description: 'Rebuild the discovery cache',
            environment: 'generic',
        );
        $registry->register($cacheRebuild);

        $publish = new CommandMetadata(
            name: 'publish:commands',
            file: '',
            description: 'Publish bundled commands to ~/.pcmd/commands/',
            environment: 'generic',
        );
        $registry->register($publish);

        $configShow = new CommandMetadata(
            name: 'config:show',
            file: '',
            description: 'Display active configuration',
            environment: 'generic',
        );
        $registry->register($configShow);
    }

    private function resourcePath(): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'commands';
    }

    private function registerBundledCommands(CommandRegistry $registry, Environment $environment, CommandLoader $loader): void
    {
        $resourceDir = $this->resourcePath();
        $scanner = new DirectoryScanner();

        $generalDir = $resourceDir . DIRECTORY_SEPARATOR . 'general';

        if (is_dir($generalDir)) {
            foreach ($scanner->scan($generalDir) as $file) {
                if ($registry->exists($file['name'])) {
                    continue;
                }

                try {
                    $metadata = new CommandMetadata(
                        name: $file['name'],
                        file: $file['path'],
                        description: '',
                        environment: 'generic',
                    );

                    $registry->register($metadata);
                    $loader->loadMetadata($metadata);
                } catch (\Throwable) {
                }
            }
        }

        if ($environment->type() === 'generic') {
            return;
        }

        $envDir = $resourceDir . DIRECTORY_SEPARATOR . $environment->type();

        if (!is_dir($envDir)) {
            return;
        }

        foreach ($scanner->scan($envDir) as $file) {
            if ($registry->exists($file['name'])) {
                continue;
            }

            try {
                $metadata = new CommandMetadata(
                    name: $file['name'],
                    file: $file['path'],
                    description: '',
                    environment: $environment->type(),
                );

                $registry->register($metadata);
                $loader->loadMetadata($metadata);
            } catch (\Throwable) {
            }
        }
    }

    private function publishBundledCommands(Context $ctx): int
    {
        $resourceDir = $this->resourcePath();
        $home = $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? '/tmp';

        if (!is_string($home)) {
            $home = '/tmp';
        }

        $userDir = $home . DIRECTORY_SEPARATOR . '.pcmd' . DIRECTORY_SEPARATOR . 'commands';
        $force = $ctx->option('force') === true;
        $group = $ctx->option('group');

        if ($group !== null && !in_array($group, ['general', 'laravel'], true)) {
            $ctx->error('Invalid group. Allowed: general, laravel.');
            return 4;
        }

        $groups = $group !== null ? [$group] : ['general', 'laravel'];
        $published = 0;
        $skipped = 0;

        foreach ($groups as $g) {
            $srcDir = $resourceDir . DIRECTORY_SEPARATOR . $g;

            if (!is_dir($srcDir)) {
                continue;
            }

            $scanner = new DirectoryScanner();
            $files = $scanner->scan($srcDir);

            foreach ($files as $file) {
                $relative = substr($file['path'], strlen($srcDir) + 1);
                $targetPath = $userDir . DIRECTORY_SEPARATOR . $g . DIRECTORY_SEPARATOR . $relative;
                $targetDir = dirname($targetPath);

                if (file_exists($targetPath) && !$force) {
                    $skipped++;
                    continue;
                }

                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

                copy($file['path'], $targetPath);
                $ctx->info('Published: ' . $g . '/' . $relative);
                $published++;
            }
        }

        $ctx->newline();
        $ctx->success("Published {$published} command(s).");

        if ($skipped > 0) {
            $ctx->warn("{$skipped} already exist(s). Use --force to overwrite.");
        }

        return 0;
    }

    private function resolveCommand(CommandRegistry $registry, Environment $environment): ?ResolvedCommand
    {
        $resolver = new CommandResolver($registry, $environment);

        $commandName = $this->argvParser->commandName();

        if ($commandName === '' || $this->argvParser->hasOption('help')) {
            return null;
        }

        $resolved = $resolver->resolve($this->argvParser);

        if ($resolved === null) {
            $suggestions = $resolver->suggest($commandName);
            $this->output->error('Unknown command: ' . $commandName);

            if ($suggestions !== []) {
                $this->output->line('');
                $this->output->line('Did you mean:');

                foreach ($suggestions as $suggestion) {
                    $this->output->line('  ' . $suggestion);
                }
            }

            return null;
        }

        return $resolved;
    }

    private function executeCommand(ResolvedCommand $resolved, Environment $environment, Config $config): int
    {
        $commandName = $resolved->metadata()->name();

        $adapter = $this->bootFrameworkAdapter($resolved, $environment);
        $hookRunner = new HookRunner();

        $builtins = [
            'help' => function (Context $ctx) {
                $cmd = new HelpCommand($this->buildRegistryForHelp($ctx));
                return $cmd->run($ctx);
            },
            'list' => function (Context $ctx) use ($environment) {
                $registry = $this->buildRegistryForHelp($ctx);
                $cmd = new ListCommand($registry, $environment);
                return $cmd->run($ctx);
            },
            'version' => function (Context $ctx) {
                $cmd = new VersionCommand();
                return $cmd->run($ctx);
            },
            'env' => function (Context $ctx) {
                $cmd = new EnvCommand();
                return $cmd->run($ctx);
            },
            'doctor' => function (Context $ctx) {
                $cmd = new DoctorCommand();
                return $cmd->run($ctx);
            },
            'cache:clear' => function (Context $ctx) {
                $cache = new DiscoveryCache();
                $cmd = new CacheCommand($cache);
                return $cmd->clear($ctx);
            },
            'cache:rebuild' => function (Context $ctx) use ($environment) {
                $cache = new DiscoveryCache();
                $cache->clear();
                $discovery = new CommandDiscovery(
                    new DirectoryScanner(),
                    cache: $cache,
                );
                $discovery->discover($environment);
                $ctx->success('Discovery cache rebuilt.');
                return 0;
            },
            'publish:commands' => function (Context $ctx) {
                return $this->publishBundledCommands($ctx);
            },
            'config:show' => function (Context $ctx) {
                $cmd = new ConfigCommand($ctx->config());
                return $cmd->show($ctx);
            },
        ];

        $pluginHooks = $this->pluginManager->hookCallables();

        $beforeHooks = array_merge(
            $hookRunner->loadBeforeHooks(),
            $pluginHooks['before'],
        );

        $afterHooks = array_merge(
            $hookRunner->loadAfterHooks(),
            $pluginHooks['after'],
        );

        $shutdownHooks = array_merge(
            $hookRunner->loadShutdownHooks(),
            $pluginHooks['shutdown'],
        );

        $result = null;
        $context = null;

        if (isset($builtins[$commandName])) {
            $context = $this->buildContext($resolved, $environment, $config);
            $result = $builtins[$commandName]($context);
        } else {
            $executor = new CommandExecutor(new CommandLoader());
            $executor->setHooks($beforeHooks, $afterHooks);

            $context = $this->buildContext($resolved, $environment, $config, $adapter);
            $result = $executor->execute($resolved, $context);
        }

        foreach ($shutdownHooks as $hook) {
            try {
                $hook($context);
            } catch (\Throwable) {
            }
        }

        return $result;
    }

    private function buildContext(ResolvedCommand $resolved, Environment $environment, Config $config, ?object $adapter = null): Context
    {
        $terminal = new Terminal(
            ansi: !$this->argvParser->hasOption('no-ansi'),
            interactive: !$this->argvParser->hasOption('no-interaction'),
            verbose: $this->argvParser->hasOption('verbose'),
            debug: $this->argvParser->hasOption('debug'),
        );

        $cwd = getcwd();

        if ($cwd === false) {
            $cwd = '.';
        }

        $home = $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? '/tmp';

        if (!is_string($home)) {
            $home = '/tmp';
        }

        $helperLoader = new HelperLoader();
        $logger = $this->container->has(LoggerInterface::class)
            ? $this->container->get(LoggerInterface::class)
            : null;

        if (!$logger instanceof LoggerInterface) {
            $logger = null;
        }

        return new Context(
            config: $config,
            terminal: $terminal,
            environment: $environment,
            resolvedCommand: $resolved,
            cwd: $cwd,
            home: $home,
            frameworkAdapter: $adapter,
            helperLoader: $helperLoader,
            logger: $logger,
        );
    }

    private function buildRegistryForHelp(Context $ctx): CommandRegistry
    {
        $env = $ctx->environment();
        $registry = new CommandRegistry();
        $this->registerBuiltinCommands($registry, $env);

        $discovery = new CommandDiscovery(new DirectoryScanner());
        $discovery->setPluginDirectories($this->pluginManager->commandDirectories());
        $discovered = $discovery->discover($env);
        $loader = new CommandLoader();

        foreach ($discovered->all() as $metadata) {
            try {
                if (in_array($metadata->name(), self::RESERVED_COMMANDS, true)) {
                    continue;
                }

                $registry->register($metadata);

                $loader->loadMetadata($metadata);
            } catch (\Throwable) {
            }
        }

        $this->registerBundledCommands($registry, $env, $loader);

        return $registry;
    }

    private function printHelp(CommandRegistry $registry): void
    {
        $this->output->line(self::NAME . ' ' . self::VERSION);
        $this->output->line('');
        $this->output->line('Usage:');
        $this->output->line('  pcmd [global-options] command [arguments] [options]');
        $this->output->line('');
        $this->output->line('Global Options:');
        $this->output->line('  --help         Display help');
        $this->output->line('  --version      Display version');
        $this->output->line('  --verbose      Verbose output');
        $this->output->line('  --quiet        Suppress output');
        $this->output->line('  --yes          Automatic yes');
        $this->output->line('');
        $this->output->line('Available commands:');

        foreach ($registry->all() as $command) {
            if ($command->hidden()) {
                continue;
            }

            $desc = $command->description();

            if ($desc !== '') {
                $this->output->line('  ' . str_pad($command->name(), 20) . $desc);
            } else {
                $this->output->line('  ' . $command->name());
            }
        }
    }

    private function bootFrameworkAdapter(ResolvedCommand $resolved, Environment $environment): ?object
    {
        $commandEnv = $resolved->metadata()->environment();

        if ($commandEnv === 'generic' || $commandEnv !== $environment->type()) {
            return null;
        }

        if ($environment->isLaravel()) {
            $adapterClass = 'Pcmd\\Framework\\Laravel\\LaravelAdapter';

            if (!class_exists($adapterClass)) {
                return null;
            }

            try {
                $adapter = new $adapterClass($environment->root());
                $adapter->boot();
                return $adapter;
            } catch (\Throwable $e) {
                $this->output->error('Failed to bootstrap Laravel: ' . $e->getMessage());
                return null;
            }
        }

        return null;
    }

    public static function version(): string
    {
        return self::VERSION;
    }

    public static function name(): string
    {
        return self::NAME;
    }
}
