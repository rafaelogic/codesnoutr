<?php

namespace Rafaelogic\CodeSnoutr\Services\UI;

use Rafaelogic\CodeSnoutr\Contracts\UI\AssistantStateServiceInterface;
use Illuminate\Support\Facades\Session;

class AssistantStateService implements AssistantStateServiceInterface
{
    protected string $sessionPrefix = 'smart_assistant_';
    
    /**
     * Open assistant with optional context
     */
    public function openAssistant(?string $context = null): void
    {
        Session::put($this->sessionPrefix . 'is_open', true);
        
        if ($context) {
            $this->setContext($context);
        }
    }

    /**
     * Close assistant
     */
    public function closeAssistant(): void
    {
        Session::put($this->sessionPrefix . 'is_open', false);
    }

    /**
     * Toggle assistant open/closed state
     */
    public function toggleAssistant(): void
    {
        if ($this->isOpen()) {
            $this->closeAssistant();
        } else {
            $this->openAssistant();
        }
    }

    /**
     * Set current context
     */
    public function setContext(string $context): void
    {
        Session::put($this->sessionPrefix . 'context', $context);
    }

    /**
     * Get current context
     */
    public function getCurrentContext(): string
    {
        return Session::get($this->sessionPrefix . 'context', 'general');
    }

    /**
     * Check if assistant is open
     */
    public function isOpen(): bool
    {
        return Session::get($this->sessionPrefix . 'is_open', false);
    }

    /**
     * Get context icon
     */
    public function getContextIcon(string $context): string
    {
        $icons = [
            'general' => 'chat-bubble-left-right',
            'security' => 'shield-check',
            'performance' => 'bolt',
            'quality' => 'star',
            'laravel' => 'code-bracket',
            'scan' => 'magnifying-glass',
            'help' => 'question-mark-circle'
        ];

        return $icons[$context] ?? $icons['general'];
    }

    /**
     * Get context display name
     */
    public function getContextName(string $context): string
    {
        $names = [
            'general' => 'General',
            'security' => 'Security',
            'performance' => 'Performance',
            'quality' => 'Code Quality',
            'laravel' => 'Laravel',
            'scan' => 'Scanning',
            'help' => 'Help & Support'
        ];

        return $names[$context] ?? ucfirst($context);
    }

    /**
     * Check if quick actions should be shown
     */
    public function shouldShowQuickActions(): bool
    {
        return Session::get($this->sessionPrefix . 'show_quick_actions', true);
    }

    /**
     * Toggle quick actions visibility
     */
    public function toggleQuickActions(): void
    {
        $current = Session::get($this->sessionPrefix . 'show_quick_actions', true);
        Session::put($this->sessionPrefix . 'show_quick_actions', !$current);
    }

    /**
     * Get state data for initialization
     */
    public function getStateData(): array
    {
        return [
            'is_open' => $this->isOpen(),
            'current_context' => $this->getCurrentContext(),
            'show_quick_actions' => $this->shouldShowQuickActions(),
            'context_icon' => $this->getContextIcon($this->getCurrentContext()),
            'context_name' => $this->getContextName($this->getCurrentContext())
        ];
    }

    /**
     * Set loading state
     */
    public function setLoading(bool $loading): void
    {
        Session::put($this->sessionPrefix . 'is_loading', $loading);
    }

    /**
     * Check if assistant is in loading state
     */
    public function isLoading(): bool
    {
        return Session::get($this->sessionPrefix . 'is_loading', false);
    }

    /**
     * Reset assistant state
     */
    public function resetState(): void
    {
        Session::forget([
            $this->sessionPrefix . 'is_open',
            $this->sessionPrefix . 'context',
            $this->sessionPrefix . 'show_quick_actions',
            $this->sessionPrefix . 'is_loading'
        ]);
    }
}