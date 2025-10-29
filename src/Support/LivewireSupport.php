<?php

namespace Nlk\Theme\Support;

use Livewire\Component;

class LivewireSupport
{
    /**
     * Check if Livewire is installed.
     *
     * @return bool
     */
    public static function isInstalled()
    {
        return class_exists(Component::class);
    }

    /**
     * Render Livewire component with theme layout.
     *
     * @param  string $component
     * @param  array  $params
     * @return string
     */
    public static function renderComponent($component, array $params = [])
    {
        if (!static::isInstalled()) {
            throw new \Exception('Livewire is not installed. Please install livewire/livewire package.');
        }

        return \Livewire\Livewire::mount($component, $params)->html();
    }

    /**
     * Check if current request is a Livewire request.
     *
     * @return bool
     */
    public static function isLivewireRequest()
    {
        if (!static::isInstalled()) {
            return false;
        }

        return request()->header('X-Livewire') !== null ||
               request()->has('_livewire') ||
               request()->isMethod('POST') && request()->has('components');
    }
}

