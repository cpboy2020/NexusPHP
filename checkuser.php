<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
parked();
$id = 0 + $_GET["id"];
int_check($id, true);
function bark($msg)
{
    global $lang_checkuser;
    stdhead();
    stdmsg($lang_checkuser['std_error'], $msg);
    stdfoot();
    exit;
}

$r = @\NexusPHP\Components\Database::query("SELECT * FROM users WHERE status = 'pending' AND id = ".\NexusPHP\Components\Database::escape($id)) or sqlerr(__FILE__, __LINE__);
$user = mysqli_fetch_array($r) or bark($lang_checkuser['std_no_user_id']);

if (get_user_class() < UC_MODERATOR) {
    if ($user[invited_by] != $CURUSER[id]) {
        bark($lang_checkuser['std_no_permission']);
    }
}

if ($user["gender"] == "Male") {
    $gender = "<img src=pic/male.png alt='Male' style='margin-left: 4pt'>";
} elseif ($user["gender"] == "Female") {
    $gender = "<img src=pic/female.png alt='Female' style='margin-left: 4pt'>";
} elseif ($user["gender"] == "N/A") {
    $gender = "<img src=pic/na.gif alt='N/A' style='margin-left: 4pt'>";
}

if ($user[added] == "0000-00-00 00:00:00") {
    $joindate = 'N/A';
} else {
    $joindate = "$user[added] (" . get_elapsed_time(strtotime($user["added"])) . " ago)";
}
  
$res = \NexusPHP\Components\Database::query("SELECT name,flagpic FROM countries WHERE id=$user[country] LIMIT 1") or sqlerr();
if (mysqli_num_rows($res) == 1) {
    $arr = mysqli_fetch_assoc($res);
    $country = "<td class=embedded><img src=pic/flag/$arr[flagpic] alt=\"$arr[name]\" style='margin-left: 8pt'></td>";
}

stdhead($lang_checkuser['head_detail_for'] . $user["username"]);

$enabled = $user["enabled"] == 'yes';
print("<p><table class=main border=0 cellspacing=0 cellpadding=0>".
"<tr><td class=embedded><h1 style='margin:0px'>" . get_username($user['id'], true, false) . "</h1></td>$country</tr></table></p><br />\n");

if (!$enabled) {
    print($lang_checkuser['text_account_disabled']);
}
?>
<table width=737 border=1 cellspacing=0 cellpadding=5>
<tr><td class=rowhead width=1%><?php echo $lang_checkuser['row_join_date'] ?></td><td align=left width=99%><?php echo $joindate;?></td></tr>
<tr><td class=rowhead width=1%><?php echo $lang_checkuser['row_gender'] ?></td><td align=left width=99%><?php echo $gender;?></td></tr>
<tr><td class=rowhead width=1%><?php echo $lang_checkuser['row_email'] ?></td><td align=left width=99%><a href=mailto:<?php echo $user[email];?>><?php echo $user[email];?></a></td></tr>
<?php
if (get_user_class() >= UC_MODERATOR and $user[ip] != '') {
    print("<tr><td class=rowhead width=1%>".$lang_checkuser['row_ip']."</td><td align=left width=99%>$user[ip]</td></tr>");
}
print("<form method=post action=takeconfirm.php?id=".htmlspecialchars($id).">");
print("<input type=hidden name=email value=$user[email]>");
print("<tr><td class=rowhead width=1%><input type=\"checkbox\" name=\"conusr[]\" value=\"" . $id . "\" checked/></td>");
print("<td align=left width=99%><input type=submit style='height: 20px' value=\"".$lang_checkuser['submit_confirm_this_user'] ."\"></form></tr></td></table>");
stdfoot();
