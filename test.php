<?
$g = new GCal();
$g->login("ACCOUNT", "PASSWORD");
$calendars = $g->getCalendars();
foreach($calendars as $cal){
    print_r($cal); echo "<br/><br/>";
}
/*
echo "<Br/>";
echo "<Br/>";
$e = $g->addEvent($calendars[1], "show title-test", "2011-05-21T03:11:02.000Z", "2011-05-22T01:02:03.000Z", "details");
$es = $g->getEvents($calendars[1], "2011-05-20T03:11:02.000Z", "", "");
foreach($es as $event){
    print_r($event); echo "<br/><br/>";
}
echo "<Br/>";
echo "<Br/>";
$new = clone $es[0];
$new->title = "HAHAHAtest";
print_r($g->editEvent($es[0], $new));
print_r($g->deleteEvent($e));
 */
?>
