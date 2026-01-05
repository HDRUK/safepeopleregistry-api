<?php

namespace Database\Seeders;

use Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Custodian;
use App\Models\Organisation;
use App\Models\CustodianUser;
use App\Traits\CommonFunctions;
use App\Traits\SeedersUtils;
use Illuminate\Database\Seeder;
use RegistryManagementController as RMC;
use App\Models\DecisionModel;
use App\Models\CustodianModelConfig;
use App\Models\Permission;
use App\Models\CustodianUserHasPermission;
use App\Models\ValidationCheck;
use App\Models\CustodianHasValidationCheck;
use App\Models\State;

class TestSeeder extends Seeder
{
    use CommonFunctions;
    use SeedersUtils;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()->instance('seeding', true);

        $this->createTestUsers();
    }

    private function createCustodians(array $custodians): void
    {
        foreach ($custodians as $custodian) {
            $i = Custodian::factory()->create([
                'name' => $custodian['name'],
                'contact_email' => $custodian['email'],
                'enabled' => 1,
                'idvt_required' => 1,
            ]);

            $decisionModels = DecisionModel::all();

            foreach ($decisionModels as $d) {
                CustodianModelConfig::create([
                    'entity_model_id' => $d->id,
                    'active' => 1,
                    'custodian_id' => $i->id,
                ]);
            }

            $iu = CustodianUser::create([
                'first_name' => $custodian['given_name'],
                'last_name' => $custodian['family_name'],
                'email' => $custodian['email'],
                'password' => null,
                'provider' => '',
                'custodian_id' => $i->id,
            ]);

            $perm = Permission::where('name', '=', 'CUSTODIAN_ADMIN')->select(['id'])->first();

            CustodianUserHasPermission::create([
                'custodian_user_id' => $iu->id,
                'permission_id' => $perm->id,
            ]);

            $validationCheckIds = ValidationCheck::pluck('id')->all();

            foreach ($validationCheckIds as $validationCheckId) {
                CustodianHasValidationCheck::create([
                    'custodian_id' => $i->id,
                    'validation_check_id' => $validationCheckId,
                ]);
            }

            RMC::createNewUser($custodian, [
                'account_type' => RMC::KC_GROUP_CUSTODIANS
            ]);

            User::where('email', $custodian['email'])->update([
                'custodian_user_id' => $iu->id
            ]);
        }
    }

    private function createTestUsers(): void
    {
        $testOrganisation = Organisation::create([
            'organisation_name' => 'Test Organisation, LTD',
            'address_1' => 'Floor 1',
            'address_2' => '10 Stratton Street',
            'town' => 'Chelsea',
            'county' => 'London',
            'country' => 'United Kingdom',
            'postcode' => 'SW1X 9LB',
            'lead_applicant_organisation_name' => 'Org User',
            'lead_applicant_email' => 'test.user+organisation@safepeopleregistry.com',
            'password' => null, // Ask LS "Flood********"
            'organisation_unique_id' => Str::random(40),
            'applicant_names' => 'Org User',
            'funders_and_sponsors' => null,
            'sub_license_arrangements' => '...',
            'verified' => false,
            'dsptk_ods_code' => '',
            'iso_27001_certified' => false,
            'ce_certified' => false,
            'ce_plus_certified' => false,
            'companies_house_no' => '012345678',
            'sector_id' => 6, // Private/Industry
            'ror_id' => null,
            'smb_status' => null,
            'organisation_size' => 1,
            'website' => null,
            'unclaimed' => 0,
        ]);

        $testOrganisation->setState(State::STATE_ORGANISATION_REGISTERED);

        $testOrganisationUsers = [
            [
                'first_name' => 'Org',
                'last_name' => 'Admin',
                'email' => "test.user+organisation@safepeopleregistry.com",
                'is_org_admin' => 1,
                'user_group' => RMC::KC_GROUP_ORGANISATIONS,
                'organisation_id' => $testOrganisation->id,
                'keycloak_id' => '5edbed6e-f610-4646-ad1d-c3faf155443a',
                'is_sro' => 1,
            ]
        ];

        $this->createUsers($testOrganisationUsers);

        $testUsers = [
            [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => "test.user+user@safepeopleregistry.com",
                'user_group' => RMC::KC_GROUP_USERS,
                'keycloak_id' => '5539052c-be47-4345-bfce-c67c6f3b82c5',
                't_and_c_agreed' => true,
                't_and_c_agreement_date' => Carbon::now(),
                'affiliations' => [
                    [
                        'organisation_id' => $testOrganisation->id,
                        'member_id' => Str::uuid(),
                        'relationship' => 'employee',
                        'from' => Carbon::now()->subYears(6)->toDateString(),
                        'to' => '',
                        'department' => 'Research & Development',
                        'role' => 'Lobbyist',
                        'email' => fake()->email(),
                        'ror' => generateRorID(),
                        'registry_id' => -1,
                    ],
                ],
            ]
        ];

        $this->createUsers($testUsers);
        $this->createUserRegistry($testUsers);
        $this->createAffiliations($testUsers);


        $adminUsers = [
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'test.user+admin@safepeopleregistry.com',
                'user_group' => RMC::KC_GROUP_ADMINS,
                'keycloak_id' => 'b483eb56-3ac4-4e72-a9ac-fd7f217f619b'
            ]
        ];

        $this->createAdminUsers($adminUsers);

        $testCustodians = [
            [
                'name' => 'Custodian Admin',
                'email' => 'test.user+custodian@safepeopleregistry.com',
                'given_name' => 'Custodian',
                'family_name' => 'Admin',
                'sub' => '1575de97-4d60-435e-bf1b-a0376b7acdc2'
            ]
        ];

        $this->createCustodians($testCustodians);
    }

    private function createAdminUsers(mixed $input): void
    {
        $this->createUsers($input);
    }
}
