<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Rafaelogic\CodeSnoutr\Models\Setting;

class DarkModeToggle extends Component
{
    public $darkMode = false;
    public $isLoading = false;

    protected $listeners = [
        'theme-changed' => 'updateTheme',
        'settings-loaded' => 'loadThemeFromSettings',
    ];

    public function mount()
    {
        $this->loadDarkModePreference();
    }

    public function render()
    {
        return view('codesnoutr::livewire.dark-mode-toggle');
    }

    protected function loadDarkModePreference()
    {
        // Load from database setting
        $darkModeSetting = Setting::where('key', 'dark_mode')->first();
        
        if ($darkModeSetting) {
            $this->darkMode = (bool) $darkModeSetting->value;
        } else {
            // Fallback to config default
            $this->darkMode = config('codesnoutr.ui.dark_mode', false);
        }

        // Emit initial theme to parent components
        $this->dispatch('theme-initialized', darkMode: $this->darkMode);
    }

    public function toggleDarkMode()
    {
        $this->isLoading = true;
        
        try {
            $this->darkMode = !$this->darkMode;
            
            // Save preference to database
            Setting::updateOrCreate(
                ['key' => 'dark_mode'],
                ['value' => $this->darkMode]
            );

            // Emit theme change event
            $this->dispatch('theme-changed', darkMode: $this->darkMode);
            
            // Update browser storage
            $this->dispatch('update-theme-storage', darkMode: $this->darkMode);

        } catch (\Exception $e) {
            // Revert on error
            $this->darkMode = !$this->darkMode;
            $this->dispatch('theme-error', message: 'Failed to save theme preference');
        } finally {
            $this->isLoading = false;
        }
    }

    public function setDarkMode($enabled)
    {
        if ($this->darkMode !== $enabled) {
            $this->toggleDarkMode();
        }
    }

    public function updateTheme($darkMode)
    {
        $this->darkMode = $darkMode;
    }

    public function loadThemeFromSettings()
    {
        $this->loadDarkModePreference();
    }

    public function getThemeClass()
    {
        return $this->darkMode ? 'dark' : 'light';
    }

    public function getToggleIcon()
    {
        return $this->darkMode ? 'sun' : 'moon';
    }

    public function getToggleLabel()
    {
        return $this->darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode';
    }

    public function getToggleText()
    {
        return $this->darkMode ? 'Light' : 'Dark';
    }
}
