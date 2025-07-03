<?php

use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;
use Hdruk\LaravelMjml\Models\EmailTemplate;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        EmailTemplate::create([
            'identifier' => 'organisation_invite_new',
            'subject' => 'Someone is requesting you join [[env(APP_NAME)]] as a new Organisation',
            'body' => '<mjml>
                                    <mj-head>
                                    <mj-html-attributes>
                                        <mj-html-attribute class="easy-email" multiple-attributes="false" attribute-name="text-color" text-color="#000000"></mj-html-attribute>
                                        <mj-html-attribute class="easy-email" multiple-attributes="false" attribute-name="font-family" font-family="-apple-system, BlinkMacSystemFont, \'Segoe UI\', \'Roboto\', \'Oxygen\', \'Ubuntu\', \'Cantarell\', \'Fira Sans\', \'Droid Sans\',\'Helvetica Neue\', sans-serif"></mj-html-attribute>
                                        <mj-html-attribute class="easy-email" multiple-attributes="false" attribute-name="font-size" font-size="14px"></mj-html-attribute>
                                        <mj-html-attribute class="easy-email" multiple-attributes="false" attribute-name="line-height" line-height="1.7"></mj-html-attribute>
                                        <mj-html-attribute class="easy-email" multiple-attributes="false" attribute-name="font-weight" font-weight="400"></mj-html-attribute>
                                        <mj-html-attribute class="easy-email" multiple-attributes="false" attribute-name="responsive" responsive="true"></mj-html-attribute>
                                    </mj-html-attributes>
                                    <mj-breakpoint width="480px" />
                                    <mj-attributes>
                                        <mj-text font-size="14px" />
                                        <mj-text line-height="1.7" />
                                        <mj-text font-weight="400" />
                                        <mj-all font-family="-apple-system, BlinkMacSystemFont, \'Segoe UI\', \'Roboto\', \'Oxygen\', \'Ubuntu\', \'Cantarell\', \'Fira Sans\', \'Droid Sans\',\'Helvetica Neue\', sans-serif" />
                                        <mj-text font-size="14px" />
                                        <mj-text color="#000000" />
                                        <mj-text line-height="1.7" />
                                        <mj-text font-weight="400" />
                                    </mj-attributes>
                                    </mj-head>
                                
                                            <mj-body background-color="#efeeea" width="600px" >
                                                
                                    <mj-wrapper padding="20px 0px 20px 0px" border="none" direction="ltr" text-align="center" >
                                    <mj-section background-repeat="repeat" background-size="auto" background-position="top center" border="none" direction="ltr" text-align="left" padding="0px 0px 0px 0px" >
                                        <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px" >
                                        <mj-image align="center" height="100px" src="[[env(BANNER_URL)]]" width="800px" padding="0px 0px 0px 0px" ></mj-image>
                                        </mj-column>
                                    </mj-section>
                                    </mj-wrapper>
                            <mj-section>
                                <mj-column>
                                    <mj-text>
                                        Hello! One of your employees or students is requesting that you create an Organisation account within [[env(APP_NAME)]].
                                    </mj-text>
                                    <mj-text>
                                        [[USER_FIRST_NAME]] [[USER_LAST_NAME]] is attempting to affiliate themselves within their [[env(APP_NAME)]] profile, but your Organisation
                                        isn\'t known to us.
                                    </mj-text>
                                    <mj-text>
                                        <strong>Why are you receiving this?</strong> We ask the user to provide an email for the invitation to be sent. If this email finds you and you\'re
                                        not the right person to complete this registration, then please forward it to the person who is. This email is generic, and not tied to you in any way.
                                    </mj-text>
                                    <mj-button href="[[env(APP_URL)]]">
                                        More information
                                    </mj-button>
                                </mj-column>
                            </mj-section>
                        </mj-body>
                    </mjml>',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        EmailTemplate::where('identifier', 'organisation_invite_new')->delete();
    }
};
