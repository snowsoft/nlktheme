<?php
/**
 * Created by PhpStorm.
 * User: Abdulkadir kar
 * Date: 11.04.2018
 * Time: 12:16
 */

namespace Nlk\Theme\Commands;
use Illuminate\Console\Command;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem as File;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class PluginsGeneratorCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'theme:plugin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate plugins structure';

    /**
     * Widget view template global.
     *
     * @var boolean
     */
    protected $global = false;

    /**
     * Repository config.
     *
     * @var Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Filesystem
     *
     * @var Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     *
     * @param \Illuminate\Config\Repository     $config
     * @param \Illuminate\Filesystem\Filesystem $files
     * @return \Nlk\Theme\Commands\WidgetGeneratorCommand
     */
    public function __construct(Repository $config, File $files)
    {
        $this->config = $config;

        $this->files = $files;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Widget class name is camel case.
        $pluginClassName = ucfirst($this->getPluginName());

        // Widget class file is camel with php extension.
        $pluginClassFile = $pluginClassName.'.php';

        // CamelCase for template.
        $pluginClassTpl = lcfirst($this->getPluginName());

        // Get class template.
        $pluginClassTemplate = $this->getTemplate('pluginClass');

        // Default create not on a global.
        $watch = 'false';

        // If not specific a theme, not a global also return an error.
        if ($this->option('global') === false and ! $this->argument('theme')){
            return $this->error('Please specific a theme name or use option -g to create as a global plugin.');
        }

        // Create as a global use -g.
        if ($this->option('global') === true){
            $watch = 'true';
        }

        $pluginNamespace = $this->config->get('theme.namespaces.plugin');

        // Prepare class template.
        $pluginClassTemplate = preg_replace(
            array('|\{pluginNamespace\}|', '|\{pluginClass\}|', '|\{pluginTemplate\}|', '|\{watch\}|'),
            array($pluginNamespace, $pluginClassName, $pluginClassTpl, $watch),
            $pluginClassTemplate
        );

        // Create plugin directory.
        if (!$this->files->isDirectory(app_path().'/Plugins')){
            $this->files->makeDirectory(app_path().'/Plugins', 0755, true);
        }

        // Widget class already exists.
        if ($this->files->exists(app_path().'/Plugins/'.$pluginClassFile)){
            return $this->error('Plugin "'.$this->getPluginName().'" is already exists.');
        }

        // Create class file.
        $this->files->put(app_path().'/Plugins/'.$pluginClassFile, $pluginClassTemplate);

        // Make file example.
        $this->makeFile('plugins/'.$pluginClassTpl.'.blade.php', $this->getTemplate('plugin.blade'));

        $this->info('Plugin "'.$this->getPluginName().'" has been created.');
    }

    /**
     * Make file.
     *
     * @param  string $file
     * @param  string $template
     * @return void
     */
    protected function makeFile($file, $template = null)
    {
        $dirname = dirname($this->getPath($file));

        // Checking directory.
        if (!$this->argument('theme') and ! $this->files->isDirectory($dirname)){
            $this->files->makeDirectory($dirname, 0755, true);
        }

        if (!$this->files->exists($this->getPath($file))){
            $this->files->put($this->getPath($file), $template);
        }
    }

    /**
     * Get root writable path.
     *
     * @param  string $path
     * @return string
     */
    protected function getPath($path)
    {
        // If not specific theme name, so plugin will creating as global.
        if (!$this->argument('theme')){
            return base_path('resources/views/'.$path);
        }

        $rootPath = $this->option('path');

        return $rootPath.'/'.$this->getTheme().'/' . $path;
    }

    /**
     * Get the plugin name.
     *
     * @return string
     */
    protected function getPluginName()
    {
        // The first character must be lower.
        return ucfirst($this->argument('name'));
    }

    /**
     * Get the theme name.
     *
     * @return string
     */
    protected function getTheme()
    {
        return strtolower($this->argument('theme'));
    }

    /**
     * Get default template.
     *
     * @param  string $template
     * @return string
     */
    protected function getTemplate($template)
    {
        $path = realpath(__DIR__.'/../templates/'.$template.'.php');

        return $this->files->get($path);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('name', InputArgument::REQUIRED, 'Name of the plugin to generate.'),
            array('theme', InputArgument::OPTIONAL, 'Theme name to generate plugin view file.')
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $path = dirname(base_path()).'/'.$this->config->get('theme.themeDir');

        return array(
            array('path', 'p', InputOption::VALUE_OPTIONAL, 'Path to theme directory.', $path),
            array('global', 'g', InputOption::VALUE_NONE, 'Create global plugin.', null)
        );
    }
    
}