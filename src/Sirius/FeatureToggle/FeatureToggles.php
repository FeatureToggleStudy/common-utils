<?php
declare(strict_types=1);

namespace CommonUtils\Sirius\FeatureToggle;

class FeatureToggles
{
    const SCHEDULER_NAME = 'scheduler';
    const SCHEDULER_DESC = 'Scheduler to process domain events';
    const SCHEDULER_ON_MSG = 'Scheduler is running';
    const SCHEDULER_OFF_MSG = 'Scheduler is currently disabled (feature toggle is off)';

    const PUBLIC_API_NAME = 'public_api';
    const PUBLIC_API_DESC = 'Public API endpoints for systems integration';
    const PUBLIC_API_ON_MSG = 'Public API endpoints are available';
    const PUBLIC_API_OFF_MSG = 'Public API endpoints are currently disabled and unavailable (feature toggle is off)';

    const GENERATE_COURT_REFERENCE_NAME = 'generate_court_reference';
    const GENERATE_COURT_REFERENCE_DESC = "Generate valid court reference when '00000000' is used";
    const GENERATE_COURT_REFERENCE_ON_MSG = "Generate valid court reference when '00000000' is used is available";
    const GENERATE_COURT_REFERENCE_OFF_MSG = "Generate valid court reference when '00000000' is used is currently disabled (feature toggle is off)";

    const CLIENT_SOURCE_FIELD_NAME = 'client_source_field';

    const FLAG_STATUS_ACTIVE = 'always-active';
    const FLAG_STATUS_INACTIVE = 'inactive';
    const FLAG_STATUS_CONDITIONAL = 'conditionally-active';

    public static function getFlagStatus(array $flagDefinition): string
    {
        $flag = $flagDefinition['default'] ?? false;

        if (isset($flagDefinition['envVar'])) {
            $envVar = $flagDefinition['envVar'];
            $flag = getenv($envVar) ? filter_var(getenv($envVar), FILTER_VALIDATE_BOOLEAN) : $flag;
        }

        return $flag ? self::FLAG_STATUS_ACTIVE : self::FLAG_STATUS_INACTIVE;
    }

    public static function getFlagConfig(string $flagName, array $flagDefinition): array
    {
        return [
            'name' => $flagName,
            'conditions' => [],
            'status' => FeatureToggles::getFlagStatus($flagDefinition)
        ];
    }
}
