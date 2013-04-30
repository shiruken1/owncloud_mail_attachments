Owncloud Mail Attachments
=========================

Developed by [Fincluster](http://fincluster.com)

**ownClud Mail Attachments** is an app for Owncloud (community edition) that allows users to mirror 
all the attachments from their email accounts (IMAP, POP3, GMail, ...) directly into their ownCloud files directory.

A cron job constantly updates mirrors fetching new mail attachments.

This app is very useful for **private users, groups and business companies** where people makes a massive use of emails,
sending tons of attachments as primary way of file sharing with their contacts.

No more huge and shared Downloads directories containing all kind of stuff (mixing private and work/business documents),
with **ownCloud Mail Attachments** files will be automatically organized and instantly accessible 
through ownCloud web access, desktop sync clients and mobile apps.

###Installation###
ownCloud 5 is required.

Download ownCloud Mail Attachments tarball, uncompress it in ownCloud apps directory and rename it in `fc_mail_attachments`.
Please refer to ownCloud wiki for cron setup:
(http://doc.owncloud.org/server/5.0/admin_manual/configuration/background_jobs.html)

###TODO###
* multiple mail accounts for each user
* support for OAuth 
* some kind of indexing for attachments based on email message body

Feel free to request us features and submit issues.


Read the announce <a href="http://fincluster.com/blog/owncloud-mail-attachments-mirror-company-mail-attachments-to-the-cloud/">here</a>
Fincluster Cloud storage for Business <a href="http://fincluster.com/cloud/">here</a>
