<?php

use Illuminate\Database\Migrations\Migration;
use Hdruk\LaravelMjml\Models\EmailTemplate;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        EmailTemplate::create([
            'identifier' => 'new_organisation_regd',
            'enabled' => 1,
            'body' => ' <mjml>
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
                        <mj-section background-repeat="repeat" background-size="auto" background-position="top center" border="none" direction="ltr" text-align="left" padding="0px 0px 0px 0px" >
                            <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px" >
                                <mj-spacer height="20px" ></mj-spacer>
                            </mj-column>
                        </mj-section>
                        <mj-section background-repeat="repeat" background-size="auto" background-position="top center" border="none" direction="ltr" text-align="left" padding="0px 0px 0px 0px" >
                            <mj-text align="left" padding="10px 25px 10px 25px" >
                                    Hello, [[env(APP_NAME)]] Admin!.
                            </mj-text>
                        </mj-section>
                        <mj-section>
                            <mj-text>
                                    [[ORGANISATION_NAME]] is attempting to register within [[env(APP_NAME)]] and needs to be reviewed.
                                </mj-text>
                        </mj-section>
                        <mj-section>
                                <mj-text>
                                    <strong>Why are you receiving this?</strong> You have been assigned an administration role within [[env(APP_NAME)]] and this
                                    alert is sent to all administrators who are responsible for verifying Organisations.
                                </mj-text>
                        </mj-section>
                        <mj-section>
                            <mj-text>
                                Please note, if you encounter any issues with this email, you can request help by emailing us at [[env(SUPPORT_EMAIL)]].
                            </mj-text>
                            <mj-text>
                                <br/>
                                Thanks! [[env(APP_NAME)]] Team.
                            </mj-text>
                        </mj-section>
                    </mj-wrapper>
                </mj-body>
            </mjml >',
            'subject' => 'Organisation review required',
            'buttons' => '',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        EmailTemplate::where('identifier', '')->delete();
    }
};
