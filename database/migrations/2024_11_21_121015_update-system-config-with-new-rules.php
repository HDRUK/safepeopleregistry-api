<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\SystemConfig;

return new class extends Migration
{
     private const VALIDATION_SCHEMA_JSON = 
    "{
        \"validationSchema\": {
            \"password\": {
                \"type\": \"string\",
                \"minLength\": 8,
                \"maxLength\": 32,
                \"pattern\": \"^(?=.*\\\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{8,32}$\"
            },
            \"email\": {
                \"type\": \"string\",
                \"pattern\": \"^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\\\.[a-zA-Z]{2,6}$\"
            },
            \"ce_certification_num\": {
                \"type\": \"string\",
                \"pattern\": \"^[\\\\w]{4}$\"
            },
            \"orc_id\": {
                \"type\": \"string\",
                \"pattern\": \"^[\\\\d]{4}-[\\\\d]{4}-[\\\\d]{4}-[\\\\d]{3}(0|9|X)$\"
            },
            \"company_number\": {
                \"type\": \"string\",
                \"pattern\": \"^((AC|ZC|FC|GE|LP|OC|SE|SA|SZ|SF|GS|SL|SO|SC|ES|NA|NZ|NF|GN|NL|NC|R0|NI|EN|\\\\d{2}|SG|FE)\\\\d{5}(\\\\d|C|R))|((RS|SO)\\\\d{3}(\\\\d{3}|\\\\d{2}[WSRCZF]|\\\\d(FI|RS|SA|IP|US|EN|AS)|CUS))|((NI|SL)\\\\d{5}[\\\\dA])|(OC(([\\\\dP]{5}[CWERTB])|([\\\\dP]{4}(OC|CU))))$\"
            },
            \"postcode\": {
                \"type\": \"string\",
                \"maxLength\": 8,
                \"pattern\": \"[A-Za-z]{1,2}[0-9Rr][0-9A-Za-z]? [0-9][ABD-HJLNP-UW-Zabd-hjlnp-uw-z]{2}\"
            }
        }
    }";

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $config = SystemConfig::where('name', 'VALIDATION_SCHEMA')->first();

        if ($config) {
            $newValue = json_decode(self::VALIDATION_SCHEMA_JSON, true);

            $config->value = json_encode($newValue);
            $config->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $config = SystemConfig::where('name', 'VALIDATION_SCHEMA')->first();

        if ($config) {
            $schema = json_decode(self::VALIDATION_SCHEMA_JSON, true);

            $validationSchema = $schema['validationSchema'] ?? [];
            $filteredSchema = array_intersect_key($validationSchema, array_flip(['password', 'email']));

            $updatedSchema = ['validationSchema' => $filteredSchema];

            $config->value = json_encode($updatedSchema);
            $config->save();
        }
    }
};
