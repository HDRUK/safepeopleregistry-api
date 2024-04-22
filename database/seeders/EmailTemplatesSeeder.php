<?php

namespace Database\Seeders;

use Hdruk\LaravelMjml\Models\EmailTemplate;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmailTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EmailTemplate::truncate();

        // Template example
        EmailTemplate::create([
            'identifier' => 'example_template',
            'subject' => 'Example Template',
            'body' => '<mjml>
            <mj-body>
              <mj-raw>
                <!-- Company Header -->
              </mj-raw>
              <mj-section background-color="#f0f0f0">
                <mj-column>
                  <mj-text font-style="italic" font-size="20px" color="#626262">Health Data Research UK</mj-text>
                </mj-column>
              </mj-section>
              <mj-raw>
                <!-- Image Header -->
              </mj-raw>
              <mj-section background-url="https://place-hold.it/600x100/000000/ffffff/grey.png" background-size="cover" background-repeat="no-repeat">
                <mj-column width="600px">
                  <mj-text align="center" color="#fff" font-size="40px" font-family="Helvetica Neue">[[HEADER_TEXT]]</mj-text>
                </mj-column>
              </mj-section>
              <mj-raw>
                <!-- Intro text -->
              </mj-raw>
              <mj-section background-color="#fafafa">
                <mj-column width="400px">
                  <mj-text font-style="italic" font-size="20px" font-family="Helvetica Neue" color="#626262">[[SUBHEADING_TEXT]]</mj-text>
                  <mj-text color="#525252">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin rutrum enim eget magna efficitur, eu semper augue semper. Aliquam erat volutpat. Cras id dui lectus. Vestibulum sed finibus lectus, sit amet suscipit nibh. Proin nec commodo purus.
                    Sed eget nulla elit. Nulla aliquet mollis faucibus.</mj-text>
                  <mj-button background-color="#F45E43" href="[[BUTTON_1_URL]]">Button 1 Text</mj-button>
                  <mj-button background-color="#F45E43" href="[[BUTTON_2_URL]]">Button 2 Text</mj-button>
                </mj-column>
              </mj-section>
              <mj-raw>
                <!-- Side image -->
              </mj-raw>
              <mj-section background-color="white">
                <mj-raw>
                  <!-- Left image -->
                </mj-raw>
                <mj-column>
                  <mj-image width="200px" src="https://place-hold.it/200x300/000000/ffffff/grey.png"></mj-image>
                </mj-column>
                <mj-raw>
                  <!-- right paragraph -->
                </mj-raw>
                <mj-column>
                  <mj-text font-style="italic" font-size="20px" font-family="Helvetica Neue" color="#626262">[[SUBHEADING_TEXT]]</mj-text>
                  <mj-text color="#525252">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin rutrum enim eget magna efficitur, eu semper augue semper. Aliquam erat volutpat. Cras id dui lectus. Vestibulum sed finibus lectus.</mj-text>
                </mj-column>
              </mj-section>
            </mj-body>
          </mjml>',
          'buttons' => '
          {
            "replacements": [
                    {
                        "placeholder": "[[BUTTON_1_URL]]",
                        "actual": "https://test.com/something1"
                    },
                    {
                        "placeholder": "[[BUTTON_2_URL]]",
                        "actual": "https://test.com/something2"
                    }
                ]
            }
          ',
        ]);
    }
}
