<?php

namespace Rafaelogic\CodeSnoutr\Services\Wizard;

use Illuminate\Support\Facades\Validator;
use Rafaelogic\CodeSnoutr\Contracts\Services\Wizard\StepNavigationServiceContract;

/**
 * Step Navigation Service
 * 
 * Handles wizard step validation, navigation, and progress management.
 */
class StepNavigationService implements StepNavigationServiceContract
{
    protected array $stepRules = [];

    public function __construct()
    {
        $this->stepRules = [
            1 => ['scanType' => 'required|in:file,directory,codebase'],
            2 => ['target' => 'required_unless:scanType,codebase'],
            3 => ['ruleCategories' => 'required|array|min:1', 'ruleCategories.*' => 'in:security,performance,quality,laravel'],
            4 => [], // Review step - no validation needed
            5 => []  // Progress step - no validation needed
        ];
    }

    /**
     * Go to a specific step
     */
    public function goToStep(int $step, int $totalSteps): bool
    {
        return $this->isStepValid($step, $totalSteps);
    }

    /**
     * Move to the next step
     */
    public function nextStep(int $currentStep, int $totalSteps): int
    {
        if ($currentStep < $totalSteps) {
            return $currentStep + 1;
        }
        
        return $currentStep;
    }

    /**
     * Move to the previous step
     */
    public function previousStep(int $currentStep): int
    {
        if ($currentStep > 1) {
            return $currentStep - 1;
        }
        
        return $currentStep;
    }

    /**
     * Validate the current step
     */
    public function validateStep(int $step, array $data, array $rules = []): bool
    {
        $stepRules = $rules ?: $this->getStepRules($step);
        
        if (empty($stepRules)) {
            return true;
        }

        $validator = Validator::make($data, $stepRules);
        
        return !$validator->fails();
    }

    /**
     * Get validation rules for a specific step
     */
    public function getStepRules(int $step): array
    {
        return $this->stepRules[$step] ?? [];
    }

    /**
     * Check if a step is valid
     */
    public function isStepValid(int $step, int $totalSteps): bool
    {
        return $step >= 1 && $step <= $totalSteps;
    }

    /**
     * Set custom rules for a step
     */
    public function setStepRules(int $step, array $rules): void
    {
        $this->stepRules[$step] = $rules;
    }

    /**
     * Get all step rules
     */
    public function getAllStepRules(): array
    {
        return $this->stepRules;
    }
}