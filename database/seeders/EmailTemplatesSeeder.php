<?php

namespace Database\Seeders;

use Hdruk\LaravelMjml\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplatesSeeder extends Seeder
{
    private $mjmlHead = '
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
  ';

    private $titleBar = '
      <mj-wrapper margin="20px 0px 20px 0px" border="none" direction="ltr" text-align="center">
        <mj-section direction="ltr" text-align="left" padding="0px" width="100%" height="100px" border="0px" background-color="#ffffff" margin-bottom="20px">
          <mj-column border="none" vertical-align="middle" padding="0px">
            <mj-image width="200px" align="left" src="[[env(REGISTRY_IMAGE_URL)]]" href="[[env(PORTAL_URL)]]" alt=""></mj-image>
          </mj-column>
          <mj-column border="none" vertical-align="middle" padding="0px 0px 0px 0px">
            <mj-text font-family="Helvetica,Arial,sans-serif" align="right" size="14px" line-height="140%">[[email.title]]</mj-text>
          </mj-column>
        </mj-section>
      </mj-wrapper>
    ';

    private $supportFooter = '
    <div>
        Please note, if you encounter any issue whilst registering you can request help by emailing [[env(SUPPORT_EMAIL)]].
        <br/><br/>
        Thanks!
        <br/>
        [[env(APP_NAME)]] Team.<br/>                         
    </div>
  ';

    private $whatIsBlurb = '
    <p>The [[env(APP_NAME)]] is a platform to enable \'safe people\' decision-making for granting access to sensitive data. Users (researchers, analysts) make profiles; Organisations make profiles and affiliate the Users (staff, students); and Data Custodians validate both Users and Organisations to gain access to sensitive data for research projects.</p>
  ';

    public function titleBar($title): string
    {
        return str_replace('[[email.title]]', $title, $this->titleBar);
    }


    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $truncate = !env('DISABLE_TRUNCATE', false);

        if ($truncate) {
            EmailTemplate::truncate();
        }

        $templates = [
          [
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
          ],
          [
            'identifier' => 'custodian_invite',
            'subject' => 'Safe People Registry | Custodian invite',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Custodian invite') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  [[custodian.name]]
                                  <div><br></div>
                                  You\'ve been invited to sign-up as a trusted Data Custodian, for the [[env(APP_NAME)]].
                                  <div><br></div>
                                  ' . $this->whatIsBlurb . '
                                  <div><br></div>
                                  ' . $this->supportFooter . '
                                </mj-text>
                              </mj-column>
                            </mj-section>
                                    
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-text align="left" padding="10px 15px 0px 15px">
                                To begin your sign-up process, please click the button below.
                                </mj-text>
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]" padding="10px 15px 15px 15px">
                                  Sign me up!
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'organisation_invite',
            'subject' => 'Safe People Registry | Organisation invite',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Organisation invite') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  [[organisation.organisation_name]]
                                  <div><br></div>
                                  You\'ve been invited to sign-up as a trusted Organisation, for the [[env(APP_NAME)]]. 
                                  <div><br></div>
                                  ' . $this->whatIsBlurb . '
                                  <div><br></div>
                                  ' . $this->supportFooter . '
                                </mj-text>
                              </mj-column>
                            </mj-section>
                                    
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-text align="left" padding="10px 15px 0px 15px">
                                To begin your sign-up process, please click the button below.
                                </mj-text>
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]" padding="10px 15px 15px 15px">
                                  Sign me up!
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'custodian_user_invite',
            'subject' => 'Safe People Registry | User invite',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('User invite') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">

                                  [[users.first_name]] [[users.last_name]]
                                  <div><br></div>
                                  You\'ve been invited to sign-up as an Approver within the [[env(APP_NAME)]] for the Data Custodian: [[custodian.name]].
                                  <div><br></div>
                                  ' . $this->whatIsBlurb . '
                                  <div><br></div>
                                  To begin your sign-up process, please click the button below.
                                  <div><br/></div>
                                  ' . $this->supportFooter . '
                                </mj-text>
                              </mj-column>
                            </mj-section>
                                    
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-text align="left" padding="10px 15px 0px 15px">
                                  Create your account by clicking the button below.
                                </mj-text>
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]" padding="10px 15px 15px 15px">
                                  Sign me up!
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'user_invite',
            'subject' => 'Safe People Registry | User invite',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('User invite') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  ' . $this->whatIsBlurb . '
                                  <div><br></div>
                                  You\'ve been added to the [[env(APP_NAME)]] by [[custodian.name]]. Please follow the link below to make a profile. [[custodian.name]] will use your [[env(APP_NAME)]] profile to validate you under the \'safe people\' criteria of the Five Safes.
                                  <div><br></div>
                                  ' . $this->supportFooter . '
                                </mj-text>
                              </mj-column>
                            </mj-section>
                                    
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-text align="left" padding="10px 15px 0px 15px">
                                Create your account by clicking the button below.
                                </mj-text>
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]" padding="10px 15px 15px 15px">
                                  Sign me up!
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'user_otp_old',
            'subject' => 'Confirm your Registry Email address',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#efeeea" width="600px" >
                          <mj-wrapper padding="20px 0px 20px 0px" border="none" direction="ltr" text-align="center">
                            <mj-section padding="0px" text-align="left">
                              <mj-column>
                                <mj-image align="center" height="auto" padding="0px 0px 0px 0px" src="https://fakeimg.pl/800x200?text=[[env(APP_NAME)]]+OTP"></mj-image>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>
                          <mj-section padding="0px" text-align="left">
                            <mj-column>
                              <mj-spacer height="20px" padding="   "></mj-spacer>
                            </mj-column>
                          </mj-section>
                          <mj-section padding="0px" text-align="left">
                            <mj-column>
                              <mj-text padding="10px 25px 10px 25px" align="left" font-size="16px" font-weight="bold">
                              Confirm your email address
                              </mj-text>
                            </mj-column>
                          </mj-section>
                          <mj-section padding="0px" text-align="left">
                            <mj-column>
                              <mj-spacer height="20px" padding="   " ></mj-spacer>
                            </mj-column>
                          </mj-section>
                          <mj-section padding="0px" text-align="left">
                            <mj-column>
                              <mj-text padding="10px 25px 10px 25px" align="left">
                              To verify your email address, please enter the code below into your web browser.
                              </mj-text>
                            </mj-column>
                          </mj-section>
                          <mj-section padding="0px" text-align="left">
                            <mj-column>
                              <mj-spacer height="20px" padding="   " ></mj-spacer>
                            </mj-column>
                          </mj-section>
                          <mj-section padding="0px" text-align="left">
                            <mj-column>
                              <mj-text padding="10px 25px 10px 25px" align="center" font-size="24px" font-style="normal" container-background-color="#000000" color="#FFFFFF" >
                              [[users.otp]]
                              </mj-text>
                            </mj-column>
                          </mj-section>
                          <mj-section padding="0px" text-align="left">
                            <mj-column>
                              <mj-text padding="10px 25px 10px 25px" align="left">
                              The above code is only valid for [[env(OTP_VALIDITY_MINUTES)]] minute(s).
                              <br><br>
                              If you didn\'t request this email, worry not, you can safely ignore it.
                              </mj-text>
                            </mj-column>
                          </mj-section>
                          <mj-section padding="0px" text-align="left">
                            <mj-column>
                              <mj-spacer height="20px" padding="   "></mj-spacer>
                            </mj-column>
                          </mj-section>
                          <mj-section padding="0px" text-align="left">
                            <mj-column>
                              <mj-text padding="10px 25px 10px 25px" align="left" >
                              [[env(APP_NAME)]] Support Team
                              <div>
                              [[env(SUPPORT_EMAIL)]]
                              </div>
                              </mj-text>
                            </mj-column>
                          </mj-section>
                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'user_email_confirm_otp',
            'subject' => 'Safe People Registry | Email verification',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Email verification') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">

                                  Verify your [[env(APP_NAME)]] Email address
                                  <div><br></div>
                                  To verify your email address, please enter the code below into your web browser.<br>
                                  [[users.otp]]
                                  <div><br></div>
                                  The above code is only valid for [[env(OTP_VALIDITY_MINUTES)]] minute(s).
                                  <div><br></div>
                                  If you didn\'t request this email, worry not, you can safely ignore it.
                                  <div><br></div>
                                  ' . $this->supportFooter . '

                                </mj-text>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'researcher_without_organisation_invite',
            'subject' => 'You\'ve been invited to join the Researcher Registry',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#efeeea" width="600px" >
                            ' . $this->titleBar . '
                            <mj-wrapper border="none" direction="ltr" text-align="center" padding="20px 0px 20px 0px" >
                              <mj-section background-repeat="repeat" background-size="auto" background-position="top center" border="none" direction="ltr" text-align="left" padding="0px 0px 0px 0px" >
                                <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px" >
                                  <mj-text align="left" padding="10px 25px 10px 25px" >
                                    [[users.first_name]] [[users.last_name]]<br><br>You\'ve been invited to sign-up as a Researcher within the [[env(APP_NAME)]] Registry system. To begin your sign-up process, please 
                                    click the button below.
                                    <div><br></div>
                                    ' . $this->supportFooter . '
                                    <div><br></div>
                                    <div><br></div>
                                    </div>
                                  </mj-text>
                                </mj-column>
                              </mj-section>
                              <mj-section background-repeat="repeat" background-size="auto" background-position="top center" border="none" direction="ltr" text-align="left" padding="0px 0px 0px 0px" >
                                <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px" >
                                  <mj-button align="center" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]" padding="10px 25px 10px 25px" >Sign me up!</mj-button>
                                </mj-column>
                              </mj-section>
                            </mj-wrapper>
                          </mj-body>
                        </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'delegate_invite',
            'subject' => 'Safe People Registry | Delegate invite',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Delegate invite') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  [[users.first_name]] [[users.last_name]]
                                  <br><br>
                                  You\'ve been invited to sign-up as a delegate user within the [[env(APP_NAME)]], by [[organisation.organisation_name]].
                                  <div><br></div>
                                  ' . $this->whatIsBlurb . '
                                  <div><br></div>
                                  To begin your sign-up process, please click the button below.
                                  <div><br></div>
                                  ' . $this->supportFooter . '
                                </mj-text>
                              </mj-column>
                            </mj-section>
                                    
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-text align="left" padding="10px 15px 0px 15px">
                                  Create your account by clicking the button below.
                                </mj-text>
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]" padding="10px 15px 15px 15px">
                                  Sign me up!
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'delegate_sponsor',
            'subject' => 'You\'re asked to verify a researcher you employ!',
            'body' => '<mjml>
                      ' . $this->mjmlHead . '
                      <mj-body background-color="#efeeea" width="600px">
                        ' . $this->titleBar . '
                        <mj-wrapper border="none" direction="ltr" text-align="center" padding="20px 0px 20px 0px">
                          <mj-section background-repeat="repeat" background-size="auto" background-position="top center" border="none" direction="ltr" text-align="left" padding="0px 0px 0px 0px">
                            <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                              <mj-text align="left" padding="10px 25px 10px 25px">[[delegate_first_name]] [[delegate_last_name]]<br/><br/>
                              As a delegate for [[organisation.organisation_name]] on the [[env(APP_NAME)]] Registry system. You are requested to verify 
                              a recent researcher registration as someone who is currently employed by [[organisation.organisation_name]]. We ask that you please confirm this to be true, by clicking the button below. That\'s all. The rest is automatic!
                              <div><br/></div>
                              <div>
                                Name: [[users.first_name]] [[users.last_name]]<br/>
                                Registered: [[users.created_at]] <br/>
                              </div><br/>
                                ' . $this->supportFooter . '
                                  <div><br></div>
                                  <div><br></div>
                                </div>
                              </mj-text>
                            </mj-column>
                          </mj-section>
                          <mj-section background-repeat="repeat" background-size="auto" background-position="top center" border="none" direction="ltr" text-align="left" padding="0px 0px 0px 0px">
                            <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                              <mj-button align="center" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]" padding="10px 25px 10px 25px">I confirm that the named Researcher above is employed by [[organisation.organisation_name]]!</mj-button>
                            </mj-column>
                          </mj-section>
                        </mj-wrapper>
                      </mj-body>
                    </mjml>',
            'buttons' => '',
          ],
          [
            'identifier' => 'pro_email_verify',
            'subject' => 'Confirm your professional email address',
            'body' => '<mjml>
                      ' . $this->mjmlHead . '
                      <mj-body background-color="#efeeea" width="600px" >
                        <mj-wrapper padding="20px 0px 20px 0px" border="none" direction="ltr" text-align="center" >
                          <mj-section padding="0px" text-align="left" >
                            <mj-column>
                              <mj-image align="center" height="auto" padding="0px 0px 0px 0px" src="https://fakeimg.pl/800x200?text=[[env(APP_NAME)]]+Pro+Email" ></mj-image>
                            </mj-column>
                          </mj-section>
                        </mj-wrapper>
                        <mj-section padding="0px" text-align="left" >
                          <mj-column>
                            <mj-spacer height="20px" padding="5px" ></mj-spacer>
                          </mj-column>
                        </mj-section>
                        <mj-section padding="0px" text-align="left" >
                          <mj-column>
                            <mj-text padding="10px 25px 10px 25px" align="left" font-size="16px" font-weight="bold" >Confirm your email address</mj-text>
                          </mj-column>
                        </mj-section>
                        <mj-section padding="0px" text-align="left" >
                          <mj-column>
                            <mj-spacer height="20px" padding="5px" ></mj-spacer>
                          </mj-column>
                        </mj-section>
                        <mj-section padding="0px" text-align="left" >
                          <mj-column >
                            <mj-text padding="10px 25px 10px 25px" align="left" >Hi [[users.first_name]]<br/>To verify your recently added professional email address, please click the button below.</mj-text>
                          </mj-column>
                        </mj-section>
                        <mj-section padding="0px" text-align="left" >
                          <mj-column>
                            <mj-spacer height="20px" padding="10px" ></mj-spacer>
                          </mj-column>
                        </mj-section>
                        <mj-section padding="0px" text-align="left" >
                          <mj-column>
                            <mj-button align="center" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]" padding="10px 25px 10px 25px">Verify Email</mj-button>
                          </mj-column>
                        </mj-section>
                        <mj-section padding="0px" text-align="left" >
                          <mj-column>
                            <mj-spacer height="20px" padding="5px" ></mj-spacer>
                          </mj-column>
                        </mj-section>
                        <mj-section padding="0px" text-align="left" >
                          <mj-column>
                            <mj-text padding="10px 25px 10px 25px" align="left" >[[env(APP_NAME)]] Support Team<div>[[env(SUPPORT_EMAIL)]]</div></mj-text>
                          </mj-column>
                        </mj-section>
                      </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'organisation_invite_new',
            'subject' => 'Someone is requesting you join [[env(APP_NAME)]] as a new Organisation',
            'body' => '<mjml>
                      ' . $this->mjmlHead . '
                      <mj-body background-color="#efeeea" width="600px" >
                        <mj-body background-color="#efeeea" width="600px" >
                          ' . $this->titleBar . '
                          <mj-wrapper border="none" direction="ltr" text-align="center" padding="20px 0px 20px 0px" >
                            <mj-section background-repeat="repeat" background-size="auto" background-position="top center" border="none" direction="ltr" text-align="left" padding="0px 0px 0px 0px" >
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px" >
                                <mj-text align="left" padding="10px 25px 10px 25px" >
                                  <mj-text>
                                      Hello! One of your employees or students is requesting that you create an Organisation account within [[env(APP_NAME)]].
                                  </mj-text>
                                  <div><br></div>
                                  <mj-text>
                                      [[USER_FIRST_NAME]] [[USER_LAST_NAME]] is attempting to affiliate themselves within their [[env(APP_NAME)]] profile, but your Organisation
                                      isn\'t known to us.
                                  </mj-text>
                                  <div><br></div>
                                  <mj-text>
                                      <strong>Why are you receiving this?</strong> We ask the user to provide an email for the invitation to be sent. If this email finds you and you\'re
                                      not the right person to complete this registration, then please forward it to the person who is. This email is generic, and not tied to you in any way.
                                  </mj-text>
                                  <div><br></div>
                                  <mj-button href="[[env(PORTAL_URL)]]">
                                      More information
                                  </mj-button>
                                  <div><br></div>
                                  ' . $this->supportFooter . '
                                  <div><br></div>
                                  </div>
                                </mj-text>
                              </mj-column>
                            </mj-section>
                            <mj-section background-repeat="repeat" background-size="auto" background-position="top center" border="none" direction="ltr" text-align="left" padding="0px 0px 0px 0px" >
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px" >
                                <mj-button align="center" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]" padding="10px 25px 10px 25px" >Sign me up!</mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>
                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'notification',
            'subject' => 'Safe People Registry | New project',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('New project') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">

                                  You\'ve been added to [[project_name]] in the [[env(APP_NAME)]] by [[custodian.name]]. You can follow the link below to see your project list and follow your validation status.
                                  <div><br></div>
                                  ' . $this->supportFooter . '

                                </mj-text>
                              </mj-column>
                            </mj-section>
                                    
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[link.project]]" padding="10px 15px 15px 15px">
                                  Go to projects
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'organisation_confirmation_needed',
            'subject' => 'Safe People Registry | Organisation confirmation needed',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Organisation confirmation needed') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">

                                  [[organisation_name]]
                                  <div><br></div>
                                  When confirming an Organisation, you are confirming that:
                                  <ul>
                                    <li>The profile (linked) of the signee of the SRO declaration matches an appropriate profile (such as a Data Protection Officer) in that Organisation.</li>
                                    <li>The SRO declaration is signed.</li>
                                    <li>The Organisation does not appear shady.</li>
                                  </ul>
                                  <div><br></div>
                                  ' . $this->supportFooter . '

                                </mj-text>
                              </mj-column>
                            </mj-section>
                                    
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-text align="left" padding="10px 15px 0px 15px">
                                Confirm the Organisation by clicking the button below.
                                </mj-text>
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[link.organisation.profile]]" padding="10px 15px 15px 15px">
                                  Confirm Organisation
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'organisation_confirmation',
            'subject' => 'Safe People Registry | Organisation confirmed',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Organisation confirmed') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">

                                  [[organisation_name]]
                                  <div><br></div>
                                  You can now begin adding Users (researchers/innovators) to the system. 
                                  <br>
                                  To begin using your profile, please click the button below.
                                  <div><br></div>
                                  ' . $this->supportFooter . '

                                </mj-text>
                              </mj-column>
                            </mj-section>
                                    
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-text align="left" padding="10px 15px 0px 15px">
                                Return to your account by clicking the button below.
                                </mj-text>
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]" padding="10px 15px 15px 15px">
                                  Sign me in!
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'delegate_sponsorship_request',
            'subject' => 'Safe People Registry | Sponsorship request',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Sponsorship request') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">

                                  [[delegate_first_name]] [[delegate_last_name]]
                                  <div><br></div>
                                  As a Delegate for [[organisation_name]] on the [[env(APP_NAME)]], you are requested to sponsor a project.
                                  <div><br></div>
                                  Name: [[users.first_name]] [[users.last_name]]<br>
                                  Project: <a href="[[env(PORTAL_URL)]]">[[project_name]]</a><br>
                                  <div><br></div>
                                  When confirming sponsorship of a project, you are formally accepting legal accountability on behalf of your Organisation for ensuring that:
                                  <ul>
                                    <li>The research project ([[project_name]]) has been reviewed and is appropriately designed, managed, and monitored.</li>
                                    <li>The Chief Investigator of the project is suitably qualified and supported to lead the research.</li>
                                    <li>All relevant legal, ethical, and regulatory responsibilities are being met by your [[organisation_name]]</li>
                                    <li>Any delegation of Sponsor responsibilities is clearly documented and agreed.</li>
                                  </ul>
                                  <br>
                                  We ask that you please confirm this to be true by clicking the button below.
                                  <div><br></div>
                                  ' . $this->supportFooter . '

                                </mj-text>
                              </mj-column>
                            </mj-section>
                                    
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-text align="left" padding="10px 15px 0px 15px">
                                I confirm sponsorship on behalf of [[organisation_name]]! of the project [[project_name]]! 
                                </mj-text>
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]" padding="10px 15px 15px 15px">
                                  Sign me up!
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'delegate_affiliation_request',
            'subject' => 'Safe People Registry | Affiliation request',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Affiliation request') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">

                                  [[delegate_first_name]] [[delegate_last_name]]
                                  <div><br></div>
                                  As a Delegate for [[organisation.organisation_name]] on the [[env(APP_NAME)]], you are requested to confirm the affiliation of a recent User registration.
                                  <div><br></div>
                                  Name: [[users.first_name]] [[users.last_name]]<br>
                                  Email: [[users.email]]<br>

                                </mj-text>
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[users.profile]]" padding="0px 15px 15px 0px">Go to user profile</mj-button>

                                <mj-text align="left" padding="10px 0px 20px 0px">
                                  When affiliating a User you are confirming that:
                                  <ul>
                                    <li>The [[env(APP_NAME)]] User profile matches that of your employee / student.</li>
                                    <li>The employee / student is an active researcher / data analyst needing to work on sensitive data.</li>
                                    <li>The Organisational email address of the User corresponds to the correct email address in your Organisation.</li>
                                  </ul>
                                  <br>
                                  We ask that you please confirm this to be true by clicking the button below.
                                  <div><br></div>
                                  ' . $this->supportFooter . '

                                </mj-text>
                              </mj-column>
                            </mj-section>
                                    
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-text align="left" padding="10px 15px 0px 15px">
                                I confirm that the affiliation of the named User (researcher/innovator) above at [[organisation.organisation_name]].
                                </mj-text>
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]" padding="10px 15px 15px 15px">
                                  Affiliate User
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'sro_application_file',
            'subject' => 'Safe People Registry | SRO Application new file uploaded',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('SRO Application new file uploaded') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">

                                  Organisation: [[organisation.organisation_name]] just uploaded a new file: [[file.name]].
                                  <div><br></div>
                                  ' . $this->supportFooter . '

                                </mj-text>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'organisation_needs_confirmation',
            'subject' => 'Safe People Registry | Organisation confirmation needed',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Organisation confirmation needed') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  You\'re asked to confirm a new Organisation on the [[env(APP_NAME)]]!
                                  <div><br></div>
                                  [[organisation.organisation_name]]
                                </mj-text>
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[ORGANISATION_PATH_PROFILE]]" padding="0px 15px 0px 0px">
                                  Organisation Profile
                                </mj-button>
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  When confirming an Organisation, you are confirming that:
                                  <ul>
                                    <li>The profile (linked) of the signee of the SRO declaration matches an appropriate profile (such as a Data Protection Officer) in that Organisation</li>
                                    <li>The SRO declaration is signed</li>
                                    <li>The Organisation does not appear shady - [[ORGANISATION_PATH_PROFILE]]</li>
                                  </ul>
                                  <div><br></div>
                                  ' . $this->whatIsBlurb . '
                                  <div><br></div>
                                  ' . $this->supportFooter . '
                                </mj-text>
                              </mj-column>
                            </mj-section>
                                    
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-text align="left" padding="10px 15px 0px 15px">
                                Confirm the Organisation by clicking the button below.
                                </mj-text>
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[ORGANISATION_PATH_PROFILE)]]" padding="10px 15px 15px 15px">
                                  Confirm Organisation
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['identifier' => $template['identifier']],
                [
                'subject' => $template['subject'],
                'body' => $template['body'],
                'buttons' => $template['buttons'] ?? '',
        ]
            );
        }
    }
}
