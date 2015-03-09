RT Mailbox
==========

This library handles all inbound and outbound emails

Mailbox module helps you configure your emails with the plugin that uses this library. Once configured properly, this module is useful to the plugin itself and its addons in many ways.

Consider this to be a generic utility which lets you connect your mailbox with any plugin. Once it is connected, this module starts parsing emails from your mailbox and delivers them to you / your addon. With those emails received from mailbox module, you can do alot many things that you can imagine.

For example, if a new email comes to `support@example.com` then mailbox module parses the email and delivers it to you. You could setup a beautiful canned reply message as a response to every email that comes to `support@example.com`. You can develop such functionality within one of your addons & it will work like a charm.

This was just one use case. There are many more things that you can achieve using this module. Please feel free to contact us in case you have a wonderful idea that we can help you with.

**Features**

- Mailbox library will give you a admin page where you can setup this module for your plugin.
- This screen is divided into two tabs:
- **Mailbox:**
	- New mailboxes can be added from this tab.
	- You will need to select mail server type, and module for which you need the mailbox to be setup.
	- Select a mail servers from the existing ones ( Checkout IMAP tab ) and fill in mailbox credentials. And this is it! Your mailbox is added.
	- Once a mailbox is added, it will be listed in the mail list. You will need to choose the default `INBOX` folder. So that mailbox module will know from where it has to read & parse emails.
	- You can also add other mail folders from where you want your emails parsed.
- **IMAP:**
	- Here is a list of existing mail servers that can be used in your plugin while adding new mailboxes.
	- We have put a few popular mail servers for you by default. These will stay in the list always even if you remove them once.
	- If you have any private mail server and you want to configure it, then it is possible as well. Just fill in required configurations for the mail server and save them. Once saved, it will appear in the list while adding new mailbox.

**Get Started**

Initialize a mailbox class object in your plugin.
```
$rt_MailBox = new Rt_Mailbox(Rt_Access_Control::$modules, Rt_Biz::$dashboard_slug, $plugin_path_for_deactivate_cron );
```
*where*

- `Rt_Access_Control::$modules` (array of modules that needed the email functionality.)

- `Rt_Biz::$dashboard_slug` (parent slug for registering email setting page. If parent slug is not given, a menu page will be added instead of submenu page under a parent.)

- `$plugin_path_for_deactivate_cron` (path of plugin's main file which is used to deactivate cron when plugin is disabled)

**Mail Parsing**

To get the parsed email use below snipet in individual plugin.

```
add_action( 'read_rt_mailbox_email_'.$modules_name,  'rt_process_email_to_output' , 10, 14 );

function rt_process_email_to_output(
	$title,
	$body,
	$fromemail,
	$mailtime,
	$allemails,
	$uploaded,
	$mailBodyText,
	$check_duplicate = true,
	$userid = false,
	$messageid = '',
	$inreplyto = '',
	$references = '',
	$rt_all_emails  = array(),
	$systemEmail = false
) {
	// PERFORM YOUR MAGIC
}
```

**Send Emails**

You can also use the same library to send emails via SMTP

```
global $rt_outbound_model;

$args = array(
	'user_id'       => $user_id,
	'fromname'      => 'rtCamp',
	'fromemail'     => '',
	'toemail'       => serialize( $toemail ),
	'ccemail'       => serialize( $ccemail ),
	'bccemail'      => serialize( $bccemail ),
	'subject'       => $subject,
	'body'          => '<h1> Hello World! </h1>',
	'attachement'   => serialize( $attachement ),
	'refrence_id'   => $refrence_id,
	'refrence_type' => $refrence_type,
);

$rt_outbound_model->add_outbound_mail( $args );
```

**Enhancement**

If you see any scope of enhancement and want to add new tabs to settings page use:

```
add_filter( 'rt_mailbox_add_tab', 'add_cols', 10, 1 );

function add_cols($tabs){
    $tabs[]=array(
        'href' => get_admin_url( null, add_query_arg( array( 'page' => Rt_Mailbox::$page_name.'&tab=mypage'  ), 'admin.php' ) ),
        'name' => __( 'My page', Rt_Mailbox::$page_name ),
        'slug' => Rt_Mailbox::$page_name.'&tab=mypage',
    ),
    return $tabs;
}
```

Adding view to setting tab:
```
add_filter( 'rt_mailbox_add_tab', 'add_temp_cols', 10, 1 );

function add_temp_cols(){
    if ( isset( $_REQUEST['tab'] ) && 'mypage' == $_REQUEST['tab'] ){
         '<h1> My page </h1>';
    }
}
```
