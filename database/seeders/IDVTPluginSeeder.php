<?php

namespace Database\Seeders;

use App\Models\IDVTPlugin;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IDVTPluginSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        IDVTPlugin::truncate();

        IDVTPlugin::create([
            'function' => 'natureOfBusiness',
            'args' => 'company, sicExclusions',
            'config' => "
                function natureOfBusiness(stdClass \$company, array \$sicExclusions) {
                    \$errors = [];
                    \$result = null;

                    \$sicCode = preg_replace('/[^0-9]/', '', \$company->natureOfBusiness);
                    \$exclusionNoted = in_array(\$sicCode, \$sicExclusions);
                    if (\$exclusionNoted) {
                        // This will need weighting depending on the level of concern
                        // surrounding nature of business within specific sic areas.
                        // Made configurable, but likely needs expanding to introduce
                        // different weighting for explicitly sanctioned business types.
                        \$errors = [
                            'field' => \$company->natureOfBusiness,
                            'match_value' => \$sicCode,
                            'error' => 'sicCode is found in exlusion list',
                        ];
                        \$result = true;
                    } else {
                        \$result = false;
                    }
                    return [
                        'result' => \$result,
                        'errors' => \$errors,
                    ];
                }
            ",
            'enabled' => 1,
        ]);

        IDVTPlugin::create([
            'function' => 'companyAddress',
            'args' => 'company, addressToCheck',
            'config' => "
                function companyAddress(stdClass \$company, string \$addressToCheck) {
                    \$errors = [];
                    \$result = false;

                    \$parts = explode(',', \$company->companyAddress);
                    \$line1 = trim(\$parts[0]);

                    if (\$line1 == \$addressToCheck) {
                        \$result = true;
                    } else {
                        \$result = false;
                        \$errors = [
                            'field' => 'companyAddress',
                            'match_value' => \$line1,
                            'error' => 'provided address line, does not match gov record: ' . \$line1,
                        ];
                    }

                    return [
                        'result' => \$result,
                        'errors' => \$errors,
                    ];
                }
            ",
            'enabled' => 1,
        ]);

        IDVTPlugin::create([
            'function' => 'companyPostcode',
            'args' => 'company, postcodeToCheck',
            'config' => "
                function companyPostcode(stdClass \$company, string \$postcodeToCheck) {
                    \$result = false;
                    \$errors = [];

                    \$parts = explode(',', \$company->companyAddress);
                    \$postcode = trim(\$parts[count(\$parts)-1]);

                    var_dump(\$postcode);
                    var_dump(\$postcodeToCheck);
                    var_dump(\$postcode == \$postcodeToCheck);

                    if (\$postcode == \$postcodeToCheck) {
                        \$result = true;
                    } else {
                        \$result = false;
                        \$errors = [
                            'field' => 'postcode',
                            'match_value' => \$postcode,
                            'error' => 'provided postcode does not match gov record: ' . \$postcode,
                        ];
                    }
                    
                    return [
                        'result' => \$result,
                        'errors' => \$errors,
                    ];
                }
            ",
            'enabled' => 1,
        ]);

        IDVTPlugin::create([
            'function' => 'companyName',
            'args' => 'company, nameToCheck',
            'config' => "
                function companyName(stdClass \$company, string \$nameToCheck) {
                    \$result = false;
                    \$errors = [];

                    if (\$company->companyName === \$nameToCheck ||
                        stripos(\$company->companyName, \$nameToCheck) !== false) {
                        \$result = true;
                    } else {
                        \$errors = [
                            'field' => 'companyName',
                            'match_value' => \$nameToCheck,
                            'error' => 'provided company name, does not match gov record',
                        ];
                    }
                    
                    return [
                        'result' => \$result,
                        'errors' => \$errors,
                    ];
                }
            ",
            'enabled' => 1,
        ]);

        IDVTPlugin::create([
            'function' => 'companyNumber',
            'args' => 'company, numberToCheck',
            'config' => "
                function companyNumber(stdClass \$company, string \$numberToCheck) {
                    \$result = false;
                    \$errors = [];

                    if (\$company->companyNumber === \$numberToCheck ||
                        strpos(\$company->companyNumber, \$numberToCheck) !== false) {
                        \$result = true;
                    } else {
                        \$errors = [
                            'field' => 'companyNumber',
                            'match_value' => \$numberToCheck,
                            'error' => 'provided company number, does not match gov record',
                        ];
                    }

                    return [
                        'result' => \$result,
                        'errors' => \$errors,
                    ];
                }
            ",
            'enabled' => 1,
        ]);

        IDVTPlugin::create([
            'function' => 'personWithSignificantControl',
            'args' => 'company',
            'config' => "
                function personWithSignificantControl(stdClass \$company) {
                    \$result = false;
                    \$errors = [];

                    if (\$company->personWithSignificantControl !== '' &&
                        \$company->personWithSignificantControlActive === 'Active') {
                        \$result = true;
                    } else {
                        \$errors = [
                            'field' => 'personWithSignificantControl',
                            'match_value' => \$company->personWithSignificantControl . ' ' . 
                                \$company->personWithSignificantControlActive,
                            'error' => 'gov record doesn not contain record of significant control',
                        ];
                    }

                    return [
                        'result' => \$result,
                        'errors' => \$errors,
                    ];
                }
            ",
            'enabled' => 1,
        ]);
    }
}
