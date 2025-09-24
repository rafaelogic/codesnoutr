<?php

namespace Rafaelogic\CodeSnoutr\Contracts\UI;

interface AssistantStateServiceInterface
{
    /**
     * Open assistant with optional context
     */
    public function openAssistant(?string $context = null): void;

    /**
     * Close assistant
     */
    public function closeAssistant(): void;

    /**
     * Toggle assistant open/closed state
     */
    public function toggleAssistant(): void;

    /**
     * Set current context
     */
    public function setContext(string $context): void;

    /**
     * Get current context
     */
    public function getCurrentContext(): string;

    /**
     * Check if assistant is open
     */
    public function isOpen(): bool;

    /**
     * Get context icon
     */
    public function getContextIcon(string $context): string;

    /**
     * Get context display name
     */
    public function getContextName(string $context): string;

    /**
     * Check if quick actions should be shown
     */
    public function shouldShowQuickActions(): bool;

    /**
     * Toggle quick actions visibility
     */
    public function toggleQuickActions(): void;

    /**
     * Get state data for initialization
     */
    public function getStateData(): array;

    /**
     * Set loading state
     */
    public function setLoading(bool $loading): void;
}