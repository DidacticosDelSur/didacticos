<?php

function showNewsletterList()
{
	global $db, $t;

	$t->set_var('base_url', HOST);
    $t->set_file("pl", "newsletter.html");
    $t->set_var("user", $_SESSION["admin_name"]);

    $t->set_block("pl", "newsletter", "_newsletter");

	$query = "SELECT * FROM newsletter";

	$result    = mysqli_query($db, $query);
    while ($row = mysqli_fetch_array($result)) {
    	$t->set_var("id", $row['id']);
    	$t->set_var("email", $row['email']);

    	$t->parse("_newsletter", "newsletter", true);
    }
}

function exportNewsletterList()
{
	global $db, $t;

	$query = "SELECT email FROM newsletter";

	$result    = mysqli_query($db, $query);

	$mails = "";
    while ($row = mysqli_fetch_array($result)) {
    	$mails .= $row['email'] . "\n";
    }

    header('Content-type: text/plain');
    header('Content-Disposition: attachment; filename="newsletter.txt"');
    echo $mails;
    exit;
}


function deleteEmailFromNewsletter($id)
{
	global $db;

	$query = "DELETE FROM newsletter WHERE id = $id LIMIT 1";
	return mysqli_query($db, $query);
}

?>