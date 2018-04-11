<?php namespace {pluginNamespace};

use Nlk\Theme\Theme;
use Nlk\Theme\Widget;

class {pluginClass} extends Plugin {

    /**
     * Widget template.
     *
     * @var string
     */
    public $template = '{pluginTemplate}';

    /**
     * Watching plugin tpl on everywhere.
     *
     * @var boolean
     */
    public $watch = {watch};

    /**
     * Arrtibutes pass from a plugin.
     *
     * @var array
     */
    public $attributes = array(
        'userId' => 9999,
        'label'  => 'Generated Widget',
    );

    /**
     * Turn on/off plugin.
     *
     * @var boolean
     */
    public $enable = true;

    /**
     * Code to start this plugin.
     *
     * @return void
     */
    public function init(Theme $theme)
    {
        // Initialize plugin.

        //$theme->asset()->usePath()->add('plugin-name', 'js/plugin-execute.js', array('jquery', 'jqueryui'));
        //$this->setAttribute('user', User::find($this->getAttribute('userId')));
    }

    /**
     * Logic given to a plugin and pass to plugin's view.
     *
     * @return array
     */
    public function run()
    {
        $label = $this->getAttribute('label');

        //$this->setAttribute('label', 'changed');

        $attrs = $this->getAttributes();

        return $attrs;
    }

}