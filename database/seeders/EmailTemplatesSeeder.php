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
        <mj-html-attribute class="easy-email" multiple-attributes="false" attribute-name="font-family" font-family="-apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans","Helvetica Neue", sans-serif"></mj-html-attribute>
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
        <mj-all font-family="-apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans","Helvetica Neue", sans-serif" />
        <mj-text font-size="14px" />
        <mj-text color="#000000" />
        <mj-text line-height="1.7" />
        <mj-text font-weight="400" />
      </mj-attributes>
    </mj-head>
  ';

    private $titleBar = '
      <mj-wrapper border="none" direction="ltr" text-align="center">
        <mj-section direction="ltr" text-align="left" padding="0px" border="0px" background-color="#ffffff">
          <mj-column width="48%" border="none" vertical-align="middle" padding="0px">
            <mj-image align="left" src="[[env(REGISTRY_IMAGE_URL)]]" href="[[env(PORTAL_URL)]]" alt=""></mj-image>
          </mj-column>
          <mj-column width="50%" border="none" vertical-align="middle" padding="0px">
            <mj-text font-family="Helvetica,Arial,sans-serif" align="right" line-height="140%">[[email.title]]</mj-text>
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
    <p>The [[env(APP_NAME)]] is a platform to enable "safe people" decision-making for granting access to sensitive data. Users (researchers, analysts) make profiles; Organisations make profiles and affiliate the Users (staff, students); and Data Custodians validate both Users and Organisations to gain access to sensitive data for research projects.</p>
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
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]?login_hint=[[registration.email]]" padding="10px 15px 15px 15px">
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
            'subject' => 'Safe People Registry | Organisation invite from [[USER_NAME]]',
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
                                  Therefore, making an Organisation account will enable Data Custodians to validate you and your staff/students to gain access to sensitive data.
                                  <div><br></div>
                                  <b>How to make an account</b>
                                  <ol>
                                    <li>
                                      <b>Identify an SRO:</b> You will need to identify a Senior Responsible Officer (or SRO). The SRO is a named individual who is responsible for your Organisation\'s Safe People Registry account. In practice, this person will vary between Organisations. This could be a member of your Organisation\'s leadership team, an appropriate Head of College, or a Head of Human Resources. Ultimately, this is the person who creates this account. While there is a single account owner, the SRO can also invite numerous Delegates across an Organisation to help administer the account day-to-day.
                                    </li>
                                    <li>
                                      <b>Forward this email to the SRO:</b> If you are not the right person for the role, forward this email to an appropriate individual, as the account will be connected with this email invitation. We recommend using an "official" email inbox, such as "headofdepartment@domain.org", or similar, to enable handover of the account as needed.
                                    </li>
                                    <li>
                                      <b>Gain legal sign-off of the SRO declaration:</b> The SRO declaration, which can be found [<a href="[[SRO_TEMPLATE_DECLARATION_URL]]">here</a>], must be signed by a legal representative (in practice, often the same individual who signs data access agreements or similar). This empowers the SRO to own the Organisation account.
                                    </li>
                                  </ol>
                                  After you have created an account and been approved, you - or Delegates within your Organisation - can then affiliate staff/students with your Organisation, which speeds up the validation process for gaining access to sensitive data.<br>

                                  This also means that you gain a project register, to see all the sensitive data projects individuals at your Organisation are working on.
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
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]?invite_code=[[inviteCode]]&login_hint=[[registration.email]]" padding="10px 15px 15px 15px">
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
                                  ' . $this->whatIsBlurb . '
                                  <div><br></div>
                                  You\'ve been added to the [[env(APP_NAME)]] by [[custodian.name]]. Please follow the link below to make a profile. [[custodian.name]] will use your [[env(APP_NAME)]] profile to validate you under the "safe people" criteria of the Five Safes.
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
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]?login_hint=[[registration.email]]" padding="10px 15px 15px 15px">
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
            'identifier' => 'organisation_user_invite',
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
                                  You\'ve been invited to sign-up as a User (researcher/innovator) within the [[env(APP_NAME)]] by [[organisation.organisation_name]].
                                  <div><br></div>
                                  ' . $this->whatIsBlurb . '
                                  <div><br></div>
                                  To create your account, please click the button below.
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
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]?login_hint=[[registration.email]]" padding="10px 15px 15px 15px">
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
            'identifier' => 'custodian_invite_approver',
            'subject' => 'Safe People Registry | Approver invite',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Approver invite') . '

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
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]?login_hint=[[registration.email]]" padding="10px 15px 15px 15px">
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
            'identifier' => 'custodian_invite_administrator',
            'subject' => 'Safe People Registry | Administrator invite',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Administrator invite') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">

                                  [[users.first_name]] [[users.last_name]]
                                  <div><br></div>
                                  You\'ve been invited to sign-up as an Administrator within the [[env(APP_NAME)]] for the Data Custodian: [[custodian.name]].
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
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]?login_hint=[[registration.email]]" padding="10px 15px 15px 15px">
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
                                  You\'ve been added to the [[env(APP_NAME)]] by [[custodian.name]]. Please follow the link below to make a profile. [[custodian.name]] will use your [[env(APP_NAME)]] profile to validate you under the "safe people" criteria of the Five Safes.
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
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]?login_hint=[[registration.email]]" padding="10px 15px 15px 15px">
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
                                  <mj-button align="center" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]?login_hint=[[registration.email]]" padding="10px 25px 10px 25px" >Sign me up!</mj-button>
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
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]?invite_code=[[inviteCode]]&login_hint=[[registration.email]]" padding="10px 15px 15px 15px">
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
            'subject' => 'You"re asked to verify a researcher you employ!',
            'body' => '<mjml>
                      ' . $this->mjmlHead . '
                      <mj-body background-color="#efeeea" width="600px">
                        ' . $this->titleBar . '
                        <mj-wrapper border="none" direction="ltr" text-align="center" padding="20px 0px 20px 0px">
                          <mj-section background-repeat="repeat" background-size="auto" background-position="top center" border="none" direction="ltr" text-align="left" padding="0px 0px 0px 0px">
                            <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                              <mj-text align="left" padding="10px 25px 10px 25px">[[delegate_first_name]] [[delegate_last_name]]<br/><br/>
                              As a delegate for [[organisation.organisation_name]] on the [[env(APP_NAME)]] Registry system. You are requested to verify 
                              a recent researcher registration as someone who is currently employed by [[organisation.organisation_name]]. We ask that you please confirm this to be true, by clicking the button below. That"s all. The rest is automatic!
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

                                  You\'ve been added to [[project_name]] in the [[env(APP_NAME)]]. You can follow the link below to see your project list and follow your validation status.
                                  <div><br></div>
                                  ' . $this->supportFooter . '

                                </mj-text>
                              </mj-column>
                            </mj-section>
                                    
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[link_project]]" padding="10px 15px 15px 15px">
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
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[ORGANISATION_PATH_PROFILE]]" padding="10px 15px 15px 15px">
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
            'identifier' => 'organisation_confirmation_with_success',
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
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]?login_hint=[[registration.email]]" padding="10px 15px 15px 15px">
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
          // [
          //   'identifier' => 'organisation_needs_confirmation',
          //   'subject' => 'Safe People Registry | Organisation confirmation needed',
          //   'body' => '<mjml>
          //               ' . $this->mjmlHead . '
          //               <mj-body background-color="#f6dff1" width="600px" >

          //                 ' . $this->titleBar('Organisation confirmation needed') . '

          //                 <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
          //                   <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
          //                     <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
          //                       <mj-text align="left" padding="20px 0px 20px 0px">
          //                         You"re asked to confirm a new Organisation on the [[env(APP_NAME)]]!
          //                         <div><br></div>
          //                         [[organisation.organisation_name]]
          //                       </mj-text>
          //                       <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[ORGANISATION_PATH_PROFILE]]" padding="0px 15px 0px 0px">
          //                         Organisation Profile
          //                       </mj-button>
          //                       <mj-text align="left" padding="20px 0px 20px 0px">
          //                         When confirming an Organisation, you are confirming that:
          //                         <ul>
          //                           <li>The profile (linked) of the signee of the SRO declaration matches an appropriate profile (such as a Data Protection Officer) in that Organisation</li>
          //                           <li>The SRO declaration is signed</li>
          //                           <li>The Organisation does not appear shady - [[ORGANISATION_PATH_PROFILE]]</li>
          //                         </ul>
          //                         <div><br></div>
          //                         ' . $this->whatIsBlurb . '
          //                         <div><br></div>
          //                         ' . $this->supportFooter . '
          //                       </mj-text>
          //                     </mj-column>
          //                   </mj-section>

          //                   <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
          //                     <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
          //                       <mj-text align="left" padding="10px 15px 0px 15px">
          //                       Confirm the Organisation by clicking the button below.
          //                       </mj-text>
          //                       <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[ORGANISATION_PATH_PROFILE)]]" padding="10px 15px 15px 15px">
          //                         Confirm Organisation
          //                       </mj-button>
          //                     </mj-column>
          //                   </mj-section>
          //                 </mj-wrapper>

          //               </mj-body>
          //             </mjml >',
          //   'buttons' => '',
          // ],
          [
            'identifier' => 'affiliation_user_professional_email_confirm',
            'subject' => 'Safe People Registry | Verify affiliation email',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Verify Affiliation email') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  Verify your [[env(APP_NAME)]] Affiliation Email address
                                  <div><br></div>
                                  A Safe People Registry affiliation has been made with this email address. If this was you, click the link below to verify your email address.
                                  If you didn\'t create this account, please contact us at [[env(SUPPORT_EMAIL)]] so that we can follow up.
                                  <div><br></div>
                                  This link will expire within [[env(OTP_AFFILIATION_VALIDITY_HOURS)]] hours.
                                  <div><br></div>
                                  ' . $this->supportFooter . '
                                </mj-text>
                              </mj-column>
                            </mj-section>
                                    
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[AFFILIATION_VERIFICATION_PATH]]" padding="10px 15px 15px 15px">
                                  Link to e-mail address verification
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'custodian_sponsorship_request',
            'subject' => 'Safe People Registry | Sponsorship request',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Sponsorship request') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  [[delegate_name]]
                                  <div><br></div>
                                  As a Delegate for [[organisation_name]] on the [[env(APP_NAME)]], you are requested to sponsor a project.
                                  <div><br></div>
                                  Name: [[data_custodian_name]]<br>
                                  Project: [[project_name]]
                                  <div><br></div>
                                  When confirming sponsorship of a project, you are formally accepting legal accountability on behalf of your Organisation for ensuring that:
                                  <ul>
                                    <li>The research project ([[project_name]]) has been reviewed and is appropriately designed, managed, and monitored.</li>
                                    <li>The Chief Investigator of the project is suitably qualified and supported to lead the research.</li>
                                    <li>All relevant legal, ethical and regulatory responsibilities are being met by your Organisation: [[organisation_name]]</li>
                                    <li>Any delegation of Sponsor responsibilities is clearly documented and agreed.</li>
                                  </ul>
                                  We ask that you confirm this to be true by following the link below to confirm sponsorship.
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
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[SPONSOR_PROJECT_URL]]" padding="10px 15px 15px 15px">
                                  Sponsor project
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'user_training_expiry_warning',
            'subject' => 'Safe People Registry | Training expiry warning',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Training expiry warning') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  Your training till expire in [[TRAINING_EXPIRE_DAYS]] days. Please update your training profile below.
                                  <div><br></div>
                                  Thanks,
                                  <br/>
                                  [[env(APP_NAME)]] Team,
                                </mj-text>
                              </mj-column>
                            </mj-section>
                                    
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]" padding="10px 15px 15px 15px">
                                  Login to Safe People Registry
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'user_training_expired',
            'subject' => 'Safe People Registry | Training expired',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Training expired') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  Your training has expired. Please update your training profile below. 
                                  <div><br></div>
                                  Thanks,
                                  <br/>
                                  [[env(APP_NAME)]] Team,
                                </mj-text>
                              </mj-column>
                            </mj-section>
                                    
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]" padding="10px 15px 15px 15px">
                                  Login to Safe People Registry
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'cust_org_training_expired',
            'subject' => 'Safe People Registry | Training expired',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Training expired') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  Your User [[training_user_name]]\'s training has expired. Please check their profile below and determine what further actions you wish to take.  
                                  <div><br></div>
                                  Thanks,
                                  <br/>
                                  [[env(APP_NAME)]] Team,
                                </mj-text>
                              </mj-column>
                            </mj-section>
                                    
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]" padding="10px 15px 15px 15px">
                                  Login to Safe People Registry
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],

          [
            'identifier' => 'org_security_compliance_expired',
            'subject' => 'Safe People Registry | Certification expired',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Certification expired') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  Your security certification, [[security_compliance_name]], has expired on [[security_compliance_expiry_date]].<br>
                                  Please update your profile below.  
                                  <div><br></div>
                                  Thanks,
                                  <br/>
                                  [[env(APP_NAME)]] Team,
                                </mj-text>
                              </mj-column>
                            </mj-section>
                                    
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]" padding="10px 15px 15px 15px">
                                  Login to Safe People Registry
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'cust_security_compliance_expired',
            'subject' => 'Safe People Registry | Certification expired',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Certification expired') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  [[organisation_name]] security certification, [[security_compliance_name]], has expired on [[security_compliance_expiry_date]].<br>
                                  Please update your profile below.  
                                  <div><br></div>
                                  Thanks,
                                  <br/>
                                  [[env(APP_NAME)]] Team,
                                </mj-text>
                              </mj-column>
                            </mj-section>
                                    
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]" padding="10px 15px 15px 15px">
                                  Login to Safe People Registry
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'organisation_invite_by_superadmin',
            'subject' => 'Safe People Registry | Organisation invite from HDR UK',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Organisation invite') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  You\'ve been invited to sign-up [[organisation.organisation_name]] as a trusted Organisation for the [[env(APP_NAME)]] by HDR UK.
                                  <div><br></div>
                                  ' . $this->whatIsBlurb . '
                                  <div><br></div>
                                  Therefore, making an Organisation account will enable Data Custodians to validate you and accelerate granting your staff/students access to sensitive data.
                                  <div><br></div>
                                  This will also give you oversight of the projects your Organisation is engaged with, which individuals at your Organisation are accessing sensitive data, and the ability to log when they leave your Organisation, automatically notifying Data Custodians to remove their access to sensitive data. After you have created an account, you will be able to add Delegates to help administer it.
                                  <div><br></div>
                                  <b>Would you like to claim your account</b>
                                  <div><br></div>
                                  Follow the link below to claim your Organisation account.
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
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]?invite_code=[[inviteCode]]&login_hint=[[registration.email]]" padding="10px 15px 15px 15px">
                                  Claim your account
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'organisation_invite_by_custodian',
            'subject' => 'Safe People Registry | Organisation invite from [[inviter_name]] on behalf of [[custodian_name]]',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Organisation contact') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  [[organisation.organisation_name]]
                                  <div><br></div>
                                  You\'ve been invited to sign-up as a trusted Organisation for the [[env(APP_NAME)]] by [[inviter_name]] on behalf of [[custodian_name]].
                                  <div><br></div>
                                  ' . $this->whatIsBlurb . '
                                  <div><br></div>
                                  Therefore, making an Organisation account will enable Data Custodians to validate you and accelerate granting your staff/students access to sensitive data. Once you have created an account, you can add other Delegates from your Organisation to help administer the account (which can be very handy where your Organisation might have multiple departments or institutes!).
                                  <div><br></div>
                                  <b>Would you like to claim this account?</b>
                                  <div><br></div>
                                  Please click the button below to send us an email and claim this Organisation account, providing some context for your role at your Organisation, so that we can invite you onboard.
                                  <div><br></div>
                                  Thanks,
                                  <div><br></div>
                                  [[env(APP_NAME)]] Team
                                </mj-text>
                              </mj-column>
                            </mj-section>

                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-text align="left" padding="10px 15px 0px 15px">
                                Learn more about the Safe People Registry by following the link below, or you can email us at 
                                </mj-text>
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="mailto:enquiries@safepeopleregistry.org?subject=Organisation%20invite%20from%20[[inviter_name_urlencoded]]&body=Hello%20Safe%20People%20Registry%20team%2C%0D%0A%0D%0AI%27d%20like%20to%20claim%20the%20organisation%20account%20of%20[[organisation_name_urlencoded]].%20I%20work%20there%20as%20a%20..." padding="10px 15px 15px 15px">
                                  Request your account
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'organisation_invite_by_user',
            'subject' => 'Safe People Registry | Organisation invite from [[inviter_name]]',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Organisation contact') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  [[organisation.organisation_name]]
                                  <div><br></div>
                                  You\'ve been invited to sign-up as a trusted Organisation for the [[env(APP_NAME)]] by [[inviter_name]].
                                  <div><br></div>
                                  ' . $this->whatIsBlurb . '
                                  <div><br></div>
                                  Therefore, making an Organisation account will enable Data Custodians to validate you and accelerate granting your staff/students access to sensitive data. Once you have created an account, you can add other Delegates from your Organisation to help administer the account (which can be very handy where your Organisation might have multiple departments or institutes!).
                                  <div><br></div>
                                  <b>Would you like to claim this account?</b>
                                  <div><br></div>
                                  Please click the button below to send us an email and claim this Organisation account, providing some context for your role at your Organisation, so that we can invite you onboard.
                                  <div><br></div>
                                  Thanks,
                                  <div><br></div>
                                  [[env(APP_NAME)]] Team
                                </mj-text>
                              </mj-column>
                            </mj-section>

                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-text align="left" padding="10px 15px 0px 15px">
                                Learn more about the Safe People Registry by following the link below, or you can email us at 
                                </mj-text>
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="mailto:enquiries@safepeopleregistry.org?subject=Organisation%20invite%20from%20[[inviter_name_urlencoded]]&body=Hello%20Safe%20People%20Registry%20team%2C%0D%0A%0D%0AI%27d%20like%20to%20claim%20the%20organisation%20account%20of%20[[organisation_name_urlencoded]].%20I%20work%20there%20as%20a%20..." padding="10px 15px 15px 15px">
                                  Request your account
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'delegate_removed_user',
            'subject' => 'Safe People Registry | You have been removed from [[organisation.organisation_name]]',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Removed from Organisation') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  Hello [[delegate_first_name]],
                                  <div><br></div>
                                  [[actor_first_name]] [[actor_last_name]] has removed you as a Delegate for <b>[[organisation.organisation_name]]</b> on the [[env(APP_NAME)]].
                                  <div><br></div>
                                  You can no longer view or manage this Organisation\'s account. Your [[env(APP_NAME)]] account has not been deleted.
                                  <div><br></div>
                                  If you think this is a mistake, please contact [[organisation.organisation_name]] directly, or email us at [[env(SUPPORT_EMAIL)]].
                                  <div><br></div>
                                  Thanks,
                                  <div><br></div>
                                  [[env(APP_NAME)]] Team
                                </mj-text>
                              </mj-column>
                            </mj-section>

                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]" padding="10px 15px 15px 15px">
                                  Go to the Safe People Registry
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'delegate_removed_organisation',
            'subject' => 'Safe People Registry | [[delegate_first_name]] [[delegate_last_name]] has been removed from [[organisation.organisation_name]]',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Delegate removed') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  Hello [[recipient_first_name]],
                                  <div><br></div>
                                  [[actor_first_name]] [[actor_last_name]] has removed [[delegate_first_name]] [[delegate_last_name]] as a Delegate for <b>[[organisation.organisation_name]]</b> on the [[env(APP_NAME)]]. They no longer have access to your Organisation\'s account.
                                  <div><br></div>
                                  You can review your Organisation\'s Delegates at any time from your [[env(APP_NAME)]] account.
                                  <div><br></div>
                                  If you weren\'t expecting this change, please contact [[env(SUPPORT_EMAIL)]].
                                  <div><br></div>
                                  Thanks,
                                  <div><br></div>
                                  [[env(APP_NAME)]] Team
                                </mj-text>
                              </mj-column>
                            </mj-section>

                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]" padding="10px 15px 15px 15px">
                                  Go to the Safe People Registry
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'sro_replaced_user',
            'subject' => 'Safe People Registry | You are no longer the SRO for [[organisation.organisation_name]]',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('SRO changed') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  Hello [[recipient_first_name]],
                                  <div><br></div>
                                  [[sro_first_name]] [[sro_last_name]] has been appointed as the Senior Responsible Officer (SRO) for <b>[[organisation.organisation_name]]</b> on the [[env(APP_NAME)]] by [[actor_first_name]] [[actor_last_name]].
                                  <div><br></div>
                                  You are no longer the SRO, but you remain a Delegate for the Organisation.
                                  <div><br></div>
                                  If you think this is a mistake, please contact [[organisation.organisation_name]] directly, or email us at [[env(SUPPORT_EMAIL)]].
                                  <div><br></div>
                                  Thanks,
                                  <div><br></div>
                                  [[env(APP_NAME)]] Team
                                </mj-text>
                              </mj-column>
                            </mj-section>

                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]" padding="10px 15px 15px 15px">
                                  Go to the Safe People Registry
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'sro_appointed_organisation',
            'subject' => 'Safe People Registry | [[sro_first_name]] [[sro_last_name]] is now the SRO for [[organisation.organisation_name]]',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('New SRO') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  Hello [[recipient_first_name]],
                                  <div><br></div>
                                  [[sro_first_name]] [[sro_last_name]] has been appointed as the Senior Responsible Officer (SRO) for <b>[[organisation.organisation_name]]</b> on the [[env(APP_NAME)]] by [[actor_first_name]] [[actor_last_name]].
                                  <div><br></div>
                                  If you weren\'t expecting this change, please contact [[env(SUPPORT_EMAIL)]].
                                  <div><br></div>
                                  Thanks,
                                  <div><br></div>
                                  [[env(APP_NAME)]] Team
                                </mj-text>
                              </mj-column>
                            </mj-section>

                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]" padding="10px 15px 15px 15px">
                                  Go to the Safe People Registry
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'sro_appointed_user',
            'subject' => 'Safe People Registry | You are now the SRO for [[organisation.organisation_name]]',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('You are now the SRO') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  Hello [[recipient_first_name]],
                                  <div><br></div>
                                  You have been appointed as the Senior Responsible Officer (SRO) for <b>[[organisation.organisation_name]]</b> on the [[env(APP_NAME)]] by [[actor_first_name]] [[actor_last_name]].
                                  <div><br></div>
                                  As SRO you are responsible for your Organisation\'s Delegates, who affiliate your employees or students. You become the SRO once you create your account using the button below and upload the [<a href="[[SRO_TEMPLATE_DECLARATION_URL]]">SRO Declaration</a>]
                                  <div><br></div>
                                  If you weren\'t expecting this change, please contact [[organisation.organisation_name]] directly, or email us at [[env(SUPPORT_EMAIL)]].
                                  <div><br></div>
                                  Thanks,
                                  <div><br></div>
                                  [[env(APP_NAME)]] Team
                                </mj-text>
                              </mj-column>
                            </mj-section>

                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/organisation/profile/details/sro" padding="10px 15px 15px 15px">
                                  Upload your SRO declaration
                                </mj-button>
                              </mj-column>
                            </mj-section>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'sro_invite',
            'subject' => 'Safe People Registry | You have been invited to be the SRO for [[organisation.organisation_name]]',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('SRO invite') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  Hello [[recipient_first_name]],
                                  <div><br></div>
                                  [[actor_first_name]] [[actor_last_name]] has invited you to be the Senior Responsible Officer (SRO) for <b>[[organisation.organisation_name]]</b> on the [[env(APP_NAME)]].
                                  <div><br></div>
                                  As SRO you will be responsible for your Organisation\'s Delegates, who affiliate your employees or students. You become the SRO once you create your account using the button below and upload the [<a href="[[SRO_TEMPLATE_DECLARATION_URL]]">SRO Declaration</a>]
                                  <div><br></div>
                                  If you weren\'t expecting this invitation, please contact [[organisation.organisation_name]] directly, or email us at [[env(SUPPORT_EMAIL)]].
                                  <div><br></div>
                                  Thanks,
                                  <div><br></div>
                                  [[env(APP_NAME)]] Team
                                </mj-text>
                              </mj-column>
                            </mj-section>

                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/[[env(PORTAL_PATH_INVITE)]]?invite_code=[[inviteCode]]&login_hint=[[registration.email]]" padding="10px 15px 15px 15px">
                                  Accept the invitation
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'sro_claimed_user',
            'subject' => 'Safe People Registry | You are now the SRO for [[organisation.organisation_name]]',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('You are now the SRO') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  Hello [[recipient_first_name]],
                                  <div><br></div>
                                  This confirms that you have accepted the invitation from [[actor_first_name]] [[actor_last_name]] and are now the Senior Responsible Officer (SRO) for <b>[[organisation.organisation_name]]</b> on the [[env(APP_NAME)]], pending upload of the SRO declaration confirming your authority.
                                  <div><br></div>
                                  As SRO you are responsible for your Organisation\'s Delegates, who affiliate your employees or students.
                                  <div><br></div>
                                  If you weren\'t expecting this, please contact [[organisation.organisation_name]] directly, or email us at [[env(SUPPORT_EMAIL)]].
                                  <div><br></div>
                                  Thanks,
                                  <div><br></div>
                                  [[env(APP_NAME)]] Team
                                </mj-text>
                              </mj-column>
                            </mj-section>
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/organisation/profile/details/sro" padding="10px 15px 15px 15px">
                                  Upload your SRO declaration
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'affiliation_ending_soon_user',
            'subject' => 'Safe People Registry | Your affiliation with [[organisation.organisation_name]] ends on [[affiliation.end_date]]',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Affiliation ending soon') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  Hello [[recipient_first_name]],
                                  <div><br></div>
                                  Your affiliation with <b>[[organisation.organisation_name]]</b> on the [[env(APP_NAME)]] ends on <b>[[affiliation.end_date]]</b>.
                                  <div><br></div>
                                  When it ends, you will be recorded as having left [[organisation.organisation_name]], and you will leave any projects you are on through this affiliation. The Data Custodians of those projects will be told.
                                  <div><br></div>
                                  If this end date is wrong, please update it from your Affiliations page, or contact [[organisation.organisation_name]] directly.
                                  <div><br></div>
                                  Thanks,
                                  <div><br></div>
                                  [[env(APP_NAME)]] Team
                                </mj-text>
                              </mj-column>
                            </mj-section>

                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/user/profile/affiliations" padding="10px 15px 15px 15px">
                                  Go to my Affiliations
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'affiliation_ended_user',
            'subject' => 'Safe People Registry | Your affiliation with [[organisation.organisation_name]] has ended',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Affiliation ended') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  Hello [[recipient_first_name]],
                                  <div><br></div>
                                  Your affiliation with <b>[[organisation.organisation_name]]</b> ended on <b>[[affiliation.end_date]]</b>, and has been recorded on the [[env(APP_NAME)]].
                                  <div><br></div>
                                  You have left any projects you were on through this affiliation, and their Data Custodians have been told. Your affiliation history has been kept.
                                  <div><br></div>
                                  If you are still at [[organisation.organisation_name]], please add a new affiliation from your Affiliations page. If you think this is a mistake, please contact [[organisation.organisation_name]] directly, or email us at [[env(SUPPORT_EMAIL)]].
                                  <div><br></div>
                                  Thanks,
                                  <div><br></div>
                                  [[env(APP_NAME)]] Team
                                </mj-text>
                              </mj-column>
                            </mj-section>

                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/user/profile/affiliations" padding="10px 15px 15px 15px">
                                  Go to my Affiliations
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'affiliation_ended_organisation',
            'subject' => 'Safe People Registry | [[user_first_name]] [[user_last_name]] has left [[organisation.organisation_name]]',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('User has left') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  Hello [[recipient_first_name]],
                                  <div><br></div>
                                  [[user_first_name]] [[user_last_name]] left <b>[[organisation.organisation_name]]</b> on <b>[[affiliation.end_date]]</b>. This was recorded on the [[env(APP_NAME)]] on [[affiliation.end_date_recorded_at]].
                                  <div><br></div>
                                  They have left any projects they were on through this affiliation, and the Data Custodians of those projects have been told. Their affiliation history with your Organisation has been kept.
                                  <div><br></div>
                                  If you think this is a mistake, please email us at [[env(SUPPORT_EMAIL)]].
                                  <div><br></div>
                                  Thanks,
                                  <div><br></div>
                                  [[env(APP_NAME)]] Team
                                </mj-text>
                              </mj-column>
                            </mj-section>

                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/organisation/profile/user-administration/employees-and-students" padding="10px 15px 15px 15px">
                                  Go to Employees and Students
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'affiliation_ended_custodian',
            'subject' => 'Safe People Registry | Action required: revoke data access for [[user_first_name]] [[user_last_name]]',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Action required: revoke access') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  Hello [[recipient_first_name]],
                                  <div><br></div>
                                  [[user_first_name]] [[user_last_name]] left <b>[[organisation.organisation_name]]</b> on <b>[[affiliation.end_date]]</b> (recorded on [[affiliation.end_date_recorded_at]]). As they were on project <b>[[project.title]]</b> through this affiliation, they have now left that project on the [[env(APP_NAME)]].
                                  <div><br></div>
                                  <b>This does not revoke any access to data they hold outside the [[env(APP_NAME)]].</b> Please revoke any data access they still have for this project, then confirm you have done so from your [[env(APP_NAME)]] home page.
                                  <div><br></div>
                                  Thanks,
                                  <div><br></div>
                                  [[env(APP_NAME)]] Team
                                </mj-text>
                              </mj-column>
                            </mj-section>

                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/data-custodian/profile/home" padding="10px 15px 15px 15px">
                                  Confirm access revoked
                                </mj-button>
                              </mj-column>
                            </mj-section>
                          </mj-wrapper>

                        </mj-body>
                      </mjml >',
            'buttons' => '',
          ],
          [
            'identifier' => 'affiliation_access_revocation_reminder',
            'subject' => 'Safe People Registry | Reminder: revoke data access for [[user_first_name]] [[user_last_name]]',
            'body' => '<mjml>
                        ' . $this->mjmlHead . '
                        <mj-body background-color="#f6dff1" width="600px" >

                          ' . $this->titleBar('Reminder: revoke access') . '

                          <mj-wrapper background-color="#ffffff" border="none" direction="ltr" text-align="center" padding="0px 20px 20px 0px">
                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 20px">
                              <mj-column border="none" vertical-align="top" padding="0px 0px 0px 0px">
                                <mj-text align="left" padding="20px 0px 20px 0px">
                                  Hello [[recipient_first_name]],
                                  <div><br></div>
                                  [[user_first_name]] [[user_last_name]] left <b>[[organisation.organisation_name]]</b> on <b>[[affiliation.end_date]]</b>, and so left project <b>[[project.title]]</b>. No one at your Data Custodian has yet confirmed that their data access has been revoked.
                                  <div><br></div>
                                  <b>Please revoke any data access they still have for this project</b>, then confirm you have done so from your [[env(APP_NAME)]] home page.
                                  <div><br></div>
                                  Thanks,
                                  <div><br></div>
                                  [[env(APP_NAME)]] Team
                                </mj-text>
                              </mj-column>
                            </mj-section>

                            <mj-section border="none" direction="ltr" text-align="left" padding="0px 0px 0px 20px">
                              <mj-column border="none" background-color="#f2f2f2" vertical-align="top" padding="0px">
                                <mj-button align="left" background-color="#bd10e0" color="#ffffff" font-weight="normal" border-radius="3px" line-height="120%" target="_blank" vertical-align="middle" border="none" text-align="center" href="[[env(PORTAL_URL)]]/data-custodian/profile/home" padding="10px 15px 15px 15px">
                                  Confirm access revoked
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
                  'deleted_at' => $template['deleted_at'] ?? null,
                ]
            );
        }

        $this->command?->info('Email templates seeded successfully.');
    }
}
