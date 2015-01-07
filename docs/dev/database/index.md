rtBiz Database Schema
=====================

### Overview

Refer to the following chart for an overview of rtBiz database and how each table serves its purpose and how they are connected to each other.

![rtBiz Database Schema](https://cloud.githubusercontent.com/assets/2236554/5629563/15a21ee6-95db-11e4-86f0-95bdee040f19.png)

### Database Tables in detail

**Attributes Library**

- *wp_attributes*

	- id - unique id, primary key of the table.
	- module_name - Module from which this attrbute is added.
	- attribute_name - name of the attribute
	- attribute_label - label to be used for attribute
	- attribute_store_as - attribute storage type. Taxonomy, Meta etc.
	- attribute_render_type attribute render type. Select dropdown menu, radio, checkbox, text etc.
	- attribute_orderby attribute query order.


- *wp_attributes_relationship*

	- id - unique id, primary key of the table.
	- attr_id - attribute id for which this relation exists.
	- post_type - post type with which this attribute is linked.
	- settings - any additional settings for the relation, if there are any.

**Mailbox Library**

- *wp_imap_server*

	- id - unique id, primary key of the table.
	- server_name - server label to be used.
	- incoming_imap_server - imap server host.
	- incoming_imap_port - imap port.
	- incoming_imap_enc - imap encryption.
	- outgoing_smtp_server - smtp server host.
	- outgoing_smtp_port - smtp port.
	- outgoing_smtp_enc - smtp encryption.


- *wp_mail_accounts*

	- id - unique id, primary key of the table.
	- user_id - WP User ID for which this mailbox is added.
	- email - Email ID.
	- type - Mailbox Type. Google OAuth, Standard IMAP Login etc.
	- imap_server - imap server id, if applicable,
	- outh_token - OAuth Token for Google OAuth or encrypted IMAP password.
	- email_data - Additional mailbox data.
	- flag - mailbox flag.
	- module - module for which this mailbox is added.
	- signature - Signature for the mailbox.
	- lastMailCount - last mail count read from this mailbox.
	- sync_status - mailbox sync status.
	- last_sync_time - last time of mailbox sync.
	- last_mail_time - last time of mail in mailbox.
	- last_mail_uid - last uid of mail in mailbox.


- *wp_mail_messageids*

	- id - unique id, primary key of the table.
	- messageid - message id of mail.
	- enttime - mail entry time.


- *wp_mail_outbound*

	- id - unique id, primary key of the table.
	- fromname - `From: Name` of email.
	- fromemail - `From: Email` of email.
	- user_id - WP_User ID.
	- toemail - `To` emails list.
	- ccemail - `CC` emails list.
	- bccemail - `BCC` emails list.
	- sendtime - time at which email to be sent.
	- subject - Email subject line.
	- body - Email Body.
	- attachement - Email attachment array
	- refrence_id - Reference ID if applicable.
	- refrence_type - Reference Type if applicable.
	- sent - Flag for sending mail. `no`, `yes`, `p`, `error`.
