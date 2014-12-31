Mailbox
=======

Mailbox module helps you configure your emails with plugin that uses this library.	Once configured properly, this module is useful to rtBiz and its addons in many ways.

Consider this to be a generic utility which lets you connect your mailbox with any plugin.	Once it is connected, this module starts parsing emails from your mailbox and delivers them to you / your addon.	With those emails received from mailbox module, you can do alot many things that you can imagine.

For example, if a new email comes to `support@example.com` then mailbox module parses the email and delivers it to you. You could setup a beautiful canned reply message as a response to every email that comes to `support@example.com`. You can develop such functionality within one of your rtBiz addon & it will work like a charm.

This was just one use case. There are many more things that you can achieve using this module. Please feel free to contact us in case you have a wonderful idea that we can help you with.

### Mailbox Settings

- Mailbox library will give you an admin page where you can setup this module for your plugin.

- This screen is divided into two tabs:

- **Mailbox:**

	- New mailboxes can be added from this tab.

	- You will need to select mail server type, and module for which you need the mailbox to be setup.

	- Select a mail servers from the existing ones ( Checkout IMAP tab ) and fill in mailbox credentials. And this is it! Your mailbox is added.

	- Once a mailbox is added, it will be listed in the mail list. You will need to choose the default `INBOX` folder. So that mailbox module will know from where it has to read & parse emails.

	- You can also add other mail folders from where you want your emails parsed.

- **IMAP:**

	- Here is a list of existing mail servers that can be used in rtBiz Mailbox modules while adding new mailboxes.
	- We have put a few popular mail servers for you by default. These will stay in the list always even if you remove them once.
	- If you have any private mail server and you want to configure it, then it is possible as well. Just fill in required configurations for the mail server and save them. Once saved, it will appear in the list while adding new mailbox.
