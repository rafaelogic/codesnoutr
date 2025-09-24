<?php

namespace Rafaelogic\CodeSnoutr\Contracts\Services\Wizard;

/**
 * Wizard Step Navigation Contract
 * 
 * Handles step validation, navigation, and progress management
 * in multi-step wizards.
 */
interface StepNavigationServiceContract
{
    /**
     * Go to a specific step
     */
    public function goToStep(int $step, int $totalSteps): bool;

    /**
     * Move to the next step
     */
    public function nextStep(int $currentStep, int $totalSteps): int;

    /**
     * Move to the previous step
     */
    public function previousStep(int $currentStep): int;

    /**
     * Validate the current step
     */
    public function validateStep(int $step, array $data, array $rules): bool;

    /**
     * Get validation rules for a specific step
     */
    public function getStepRules(int $step): array;

    /**
     * Check if a step is valid
     */
    public function isStepValid(int $step, int $totalSteps): bool;
}