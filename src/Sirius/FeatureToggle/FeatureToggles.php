<?php
declare(strict_types=1);

namespace CommonUtils\Sirius\FeatureToggle;

class FeatureToggles
{
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
