Mailbox
=======

rtBiz provides one of the coolest feature in the form of mailbox.

All user needs to do is add a mailbox of his choice.

- Select a mail server. ( Google, Yahoo or Outlook etc. He may add any private mail server as well. )
- Fill in mailbox credential. ( Username & password. Howdy! No worries. We store password in encrypted form. )
- Select an rtBiz module for which you want a mailbox setup.

Once user setup a mailbox, rtBiz Mailbox module will start reading mails from your mailbox and once it gets a parsed email, it will initiate an action with this email. And now, relevant module will hook into this action to perform any task with this email. Task can be anythin such as:

- Create a new ticket from the email. (Helpdesk)
- Create a new lead from the email. (CRM)
- Add a new followup to existing ticket/lead (Helpdesk/CRM)

rtBiz uses `Rt_Mailbox` library class from rtLib. Following are the attirbutes/methods/classes that Rt_Mailbox exposes for use.

**Classes**

- `Rt_Mailbox` : This class does the basic CRUD operations on UI for Mailboxes.

	- Adds an admin page for Mailbox
	- Performs DB update if required.
	- Initializes all the db model classes in use
	- Initializes WordPress crons for mail parsing & mail delivery.
	- Initializes other settings classes for Server configurations and mail parsing.


- `Rt_Mail_Cron` : This class defines two  WordPress crons for mail parsing & mail delivery.

	- `rt_parse_email_cron` : This cron gets triggered every 5 minutes. It reads all the mailboxes that are registered with mailbox library. It parses all the unread mails and pass it on to relevant module for further tasks.
	- `rt_send_email_cron` : This cron gets triggered every minute. It is nothing but a dumb mail queue. It fetched all pending emails from the db queue table and delivers to the appropriate delivery system. After that it changes the email status to `sent` in the queue. If any error occurs while sending the email, then the status will be set to `error`.


- `Rt_Zend_Mail` : This class is a wrapper class for operating upon emails from the mailbox. It uses ZendMail library on the base.

	- Provides IMAP / Google OAuth login
	- Raw `send_mail` function
	- Raw `read_email` function
	- Raw `parse_email` function


- `Rt_Mail_Settings` : class with extra helpder functions related to mail settings.

- `RT_Setting_Imap_Server` : class that handles IMAP servers configuration CRUDs and template for UI.

- `RT_Setting_Inbound_Email` : class that handles UI templates for Mailbox rendering and its CRUD.

Other modules might take help of following snippet in order to use Mailbox module. So basically `Rt_Mailbox` handles all inbound and outbound emails of yours if configured properly.

Initialize mailbox object.

	/**
	 * Rt_Access_Control::$modules : array of modules that needed the email functionality.
	 * Rt_Biz::$dashboard_slug : parent slug for registering email setting page
	 * $plugin_path_for_deactivate_cron : path of plugin's main file which is used to deactivate cron when plugin is disabled
	 */
	$rt_MailBox = new Rt_Mailbox(Rt_Access_Control::$modules, Rt_Biz::$dashboard_slug, $plugin_path_for_deactivate_cron );

To get the parsed email use below snipet in individual plugin.

	add_action( 'read_rt_mailbox_email_'.$modules_name, array( $this, 'process_email_to_output' ), 10, 14 );

So you can see this variable named `$module_name` here. This is nothing but the slug name with which the module is registered in rtBiz.

It means at any single point, emails from one mailbox can be passed to only one module to read and process. That way we can maintain one-to-one relationship with mailbox and module to parse the emails.

With this action, module will receive a bunch of parameters that are necessary in parsing/processing an email.

- `$email_subject` - Can be used as entity title
- `$html_body` - Can go into entity description or followup content
- `$from_email` - Person who sent the email
- `$mail_time` - Time at which email was sent
- `$all_emails` - Any additional emails from CC / BCC headers
- `$uploaded` - Mail Attachments if there are any. (Inline attachments are handled here.)
- `$plain_text_body` - this is passed for reference
- `$check_duplicate` - passed as `true` becasue we would want to check for duplicate mails. Prevents duplicate lead/ticket generation.
- `$user_id` - WP_User ID of Assignee to whom entity to be assigned. Optional. Default : `false`
- `$message_id` - `''`
- `$in_reply_to` - `''`
- `$references` - `''`
- `$rt_all_emails` - `array()`
- `$systemEmail` - `false`
