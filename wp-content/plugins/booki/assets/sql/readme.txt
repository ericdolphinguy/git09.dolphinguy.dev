Things to remember, because I keep forgetting :P

1) Ensure we've removed all back ticks from the auto generated SQL.
2) Ensure there isn't any redundant spacing between SQL statements.
3) Enter in WP_DEBUG mode, in case something fails, you will get notifications.
4) Upgrade apparently just works, just throw your updated sql and dbDelta does the rest, oh yeah! 
	I did get a notice if primary key is in the create table statement, so consider removing the PK statements in the upgrade scripts.
5) Grab a beer, enjoy the rest of the day.

Copyright @ 2014  Alessandro Zifiglio. All rights reserved.