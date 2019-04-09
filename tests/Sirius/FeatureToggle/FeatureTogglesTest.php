<?php
declare(strict_types=1);

namespace CommonUtilsTest\Sirius\FeatureToggle;

use CommonUtils\Sirius\FeatureToggle\FeatureToggles;
use PHPUnit_Framework_TestCase as TestCase;

class FeatureTogglesTest extends TestCase
{
    /**
     * @dataProvider flagDefinitionProvider
     *
     * @param array $definition
     * @param callable|null $callback
     * @param string $expected
     */
    public function test_it_can_read_the_flag_status(array $definition, ?callable $callback, string $expected)
    {
        if (null !== $callback) {
            $callback();
        }

        self::assertSame($expected, FeatureToggles::getFlagStatus($definition));
    }

    /**
     * @dataProvider flagDefinitionProvider
     *
     * @param array $definition
     * @param callable|null $callback
     * @param string $expectedStatus
     */
    public function test_it_can_read_the_flag_definition(array $definition, ?callable $callback, string $expectedStatus)
    {
        $name = $definition['envVar'];
        if (null !== $callback) {
            $callback();
        }

        $config = FeatureToggles::getFlagConfig($name, $definition);
        self::assertSame($name, $config['name']);
        self::assertSame($expectedStatus, $config['status']);
    }

    public function flagDefinitionProvider()
    {
        return [
            [
                [
                    'envVar' => 'OPG_SIRIUS_FEATURE_TOGGLE_A',
                    'default' => false,
                ],
                function () {
                    putenv('OPG_SIRIUS_FEATURE_TOGGLE_A=true');
                },
                FeatureToggles::FLAG_STATUS_ACTIVE,
            ],
            [
                [
                    'envVar' => 'OPG_SIRIUS_FEATURE_TOGGLE_B',
                    'default' => true,
                ],
                null,
                FeatureToggles::FLAG_STATUS_ACTIVE,
            ],
            [
                [
                    'envVar' => 'OPG_SIRIUS_FEATURE_TOGGLE_C',
                    'default' => true,
                ],
                function () {
                    putenv('OPG_SIRIUS_FEATURE_TOGGLE_C=false');
                },
                FeatureToggles::FLAG_STATUS_INACTIVE,
            ],
            [
                [
                    'envVar' => 'OPG_SIRIUS_FEATURE_TOGGLE_D',
                    'default' => false,
                ],
                null,
                FeatureToggles::FLAG_STATUS_INACTIVE,
            ],
        ];
    }
}
