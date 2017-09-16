<?php
chdir(dirname(__FILE__)."/../");
define("CONSOLE_MODE", true);
define('PUBLIC_FOLDER', 'public');
include "init.php";

session_commit(); // we don't need sessions
@set_time_limit(0); // don't limit execution of cron, if possible


$account_ids_csv = array_var($argv, 1);

$account_ids = explode(',', $account_ids_csv);

foreach ($account_ids as $account_id) {
	
	$account = MailAccounts::findById($account_id);
	if ($account instanceof MailAccount && $account->getIsImap()) {
		//file_put_contents(CACHE_DIR."/sync.txt", date("H:i:s")." - antes de sync $account_id\n", FILE_APPEND);
	
		CompanyWebsite::instance()->logUserIn(Contacts::findById($account->getContactId()));
		//file_put_contents(CACHE_DIR."/sync.txt", date("H:i:s")." - lalala $account_id\n", FILE_APPEND);
	
		$sync_controller = new ImapSynchronizationController();
		$changes = $sync_controller->search_imap_folder_changes($account->getId());
		
		$updated_uids_count = array_var($changes, 'updated_uids_count');
		$mailbox_changes = array_var($changes, 'mailbox_changes');
		
		$ui_update_val = 0;
		if ($updated_uids_count > 0) {
			$ui_update_val = 1;
		}
		if (count($mailbox_changes['new_mailbox_names']) > 0 || count($mailbox_changes['deleted_mailboxes']) > 0) {
			$ui_update_val = 2;
		}
		
		
		// if updated data then set the need_ui_update in true for all users of this account
		if ($ui_update_val > 0) {
			DB::execute("UPDATE ".TABLE_PREFIX."mail_account_contacts 
					SET need_ui_update=$ui_update_val WHERE account_id=$account_id");
		}
		
	}
}