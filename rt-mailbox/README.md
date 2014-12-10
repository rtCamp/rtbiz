RT Mailbox
==========

handles  all inbound and outbound emails


```
$rt_MailBox = new Rt_Mailbox(Rt_Access_Control::$modules, Rt_Biz::$dashboard_slug, $plugin_path_for_deactivate_cron );
```
`Rt_Access_Control::$modules` (array of modules that needed the email functionality.)

`Rt_Biz::$dashboard_slug` (parent slug for registering email setting page)

`$plugin_path_for_deactivate_cron` (path of plugin's main file which is used to deactivate cron when plugin is disabled)


To get the parsed email use below snipet in individual plugin.

`add_action( 'read_rt_mailbox_email_'.$modules_name, array( $this, 'process_email_to_output' ), 10, 14 );`


To send email
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

Adding tabs to settings page use:
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
            echo '<h1> My page </h1>';
        }
}
```