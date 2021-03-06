<?php
require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
registration_check('invitesystem', true, false);
if (get_user_class() < $sendinvite_class) {
    stderr($lang_takeinvite['std_error'], $lang_takeinvite['std_invite_denied']);
}
if ($CURUSER['invites'] < 1) {
    stderr($lang_takeinvite['std_error'], $lang_takeinvite['std_no_invite']);
}
function bark($msg)
{
    stdhead();
    stdmsg($lang_takeinvite['head_invitation_failed'], $msg);
    stdfoot();
    exit;
}

$id = $CURUSER[id];
$email = htmlspecialchars(trim($_POST["email"]));
$email = safe_email($email);
if (!$email) {
    bark($lang_takeinvite['std_must_enter_email']);
}
if (!check_email($email)) {
    bark($lang_takeinvite['std_invalid_email_address']);
}
if (EmailBanned($email)) {
    bark($lang_takeinvite['std_email_address_banned']);
}

if (!EmailAllowed($email)) {
    bark($lang_takeinvite['std_wrong_email_address_domains'].allowedemails());
}

$body = str_replace("<br />", "<br />", nl2br(trim(strip_tags($_POST["body"]))));
if (!$body) {
    bark($lang_takeinvite['std_must_enter_personal_message']);
}


// check if email addy is already in use
$a = (@mysqli_fetch_row(@\NexusPHP\Components\Database::query("select count(*) from users where email=".\NexusPHP\Components\Database::escape($email)))) or die(\NexusPHP\Components\Database::error());
if ($a[0] != 0) {
    bark($lang_takeinvite['std_email_address'].htmlspecialchars($email).$lang_takeinvite['std_is_in_use']);
}
$b = (@mysqli_fetch_row(@\NexusPHP\Components\Database::query("select count(*) from invites where invitee=".\NexusPHP\Components\Database::escape($email)))) or die(\NexusPHP\Components\Database::error());
if ($b[0] != 0) {
    bark($lang_takeinvite['std_invitation_already_sent_to'].htmlspecialchars($email).$lang_takeinvite['std_await_user_registeration']);
}

$ret = \NexusPHP\Components\Database::query("SELECT username FROM users WHERE id = ".\NexusPHP\Components\Database::escape($id)) or sqlerr();
$arr = mysqli_fetch_assoc($ret);

$hash  = md5(mt_rand(1, 10000).$CURUSER['username'].TIMENOW.$CURUSER['passhash']);

$title = $SITENAME.$lang_takeinvite['mail_tilte'];

\NexusPHP\Components\Database::query("INSERT INTO invites (inviter, invitee, hash, time_invited) VALUES ('".\NexusPHP\Components\Database::real_escape_string($id)."', '".\NexusPHP\Components\Database::real_escape_string($email)."', '".\NexusPHP\Components\Database::real_escape_string($hash)."', " . \NexusPHP\Components\Database::escape(date("Y-m-d H:i:s")) . ")");
\NexusPHP\Components\Database::query("UPDATE users SET invites = invites - 1 WHERE id = ".\NexusPHP\Components\Database::real_escape_string($id)."") or sqlerr(__FILE__, __LINE__);

$message = <<<EOD
{$lang_takeinvite['mail_one']}{$arr[username]}{$lang_takeinvite['mail_two']}
<b><a href="javascript:void(null)" onclick="window.open('http://$BASEURL/signup.php?type=invite&invitenumber=$hash')">{$lang_takeinvite['mail_here']}</a></b><br />
http://$BASEURL/signup.php?type=invite&invitenumber=$hash
<br />{$lang_takeinvite['mail_three']}$invite_timeout{$lang_takeinvite['mail_four']}{$arr[username]}{$lang_takeinvite['mail_five']}<br />
$body
<br /><br />{$lang_takeinvite['mail_six']}
EOD;

sent_mail($email, $SITENAME, $SITEEMAIL, $title, $message, "invitesignup", false, false, '', get_email_encode(get_langfolder_cookie()));
//this email is sent only when someone give out an invitation

header("Refresh: 0; url=invite.php?id=".htmlspecialchars($id)."&sent=1");
?> 
  
    

