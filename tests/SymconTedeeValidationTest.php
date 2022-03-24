<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/Validator.php';

class SymconTedeeValidationTest extends TestCaseSymconValidation
{
    public function testValidateSymconTedee(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }

    public function testValidateConfiguratorModule(): void
    {
        $this->validateModule(__DIR__ . '/../Configurator');
    }

    public function testValidateSmartLockModule(): void
    {
        $this->validateModule(__DIR__ . '/../SmartLock');
    }

    public function testValidateSplitterModule(): void
    {
        $this->validateModule(__DIR__ . '/../Splitter');
    }
}