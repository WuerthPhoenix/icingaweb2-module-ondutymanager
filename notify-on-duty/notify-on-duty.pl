#!/usr/bin/perl
# nagios: +epn
#
# notify-on-duty.pl - Notify script with own MySQL DB to notify
#                     over SMS the right contact to a certain time.
#
# Copyright (C) 2018 Juergen Vigna
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
#
# Report bugs to:  juergen.vigna@wuerth-phoenix.com
#
#
use strict;
use warnings;
use vars qw($PROGNAME);

use Data::Dumper;
use Getopt::Long;
use Date::Parse;
use DBI;
use POSIX;
use File::Basename;

$PROGNAME = basename($0);
my $VERSION  = "1.0.0";
sub send_notification;
sub print_help;
sub print_usage;

my ($opt_h,$opt_V);
my $team = undef;
my $defaultpager = undef;
my $message = undef;
my $datetime = undef;
my $mysql_host = "localhost";
my $mysql_onduty_database  = "notify_onduty";
my $mysql_monarch_database = "monarch";
my $mysql_username = "notify";
my $mysql_password = "onduty";
my $opt_verbose = undef;
my @opt_debug;
my $opt_testonly = undef;
my $notifycmd = "/etc/nagios/neteye/sms/smssend.sh '\@CONTACT\@' '\@MESSAGE\@'";

use Getopt::Long;
&Getopt::Long::config('bundling');
GetOptions(
        "V"   => \$opt_V,        "version"          => \$opt_V,
        "h"   => \$opt_h,        "help"             => \$opt_h,
	"T=s" => \$team,         "team=s"           => \$team,
	"P=s" => \$defaultpager, "pager=s"          => \$defaultpager,
	"M=s" => \$message,      "message=s"        => \$message,
	"d=s" => \$datetime,     "datetime=s"       => \$datetime,
	"N=s" => \$notifycmd,    "notifycmd=s"      => \$notifycmd,
                                 "mysql_host=s"     => \$mysql_host,
                                 "mysql_database=s" => \$mysql_onduty_database,
                                 "mysql_username=s" => \$mysql_username,
                                 "mysql_password=s" => \$mysql_password,
        "v"   => \$opt_verbose,  "verbose"          => \$opt_verbose,
        "D"   => \@opt_debug,    "debug"            => \@opt_debug,
        "test" => \$opt_testonly, help='Test mode, it only shows the message but does not send it'  
);

# -h & --help print help
if ($opt_h) { print_help(); exit 0; }
# -V & --version print version
if ($opt_V) { printf "%s, Version %s\n",$PROGNAME, $VERSION; exit 0; }

if (!defined($team)) {
	print_help();
	print "Please specify the team to send this notification to, aborting!\n";
	exit 1;
}

if (!defined($message)) {
	print_help();
	print "Please specify the message to send in this notification, aborting!\n";
	exit 1;
}

my $dbnotify  = "DBI:mysql:database=$mysql_onduty_database;host=$mysql_host";
my $dbmonarch = "DBI:mysql:database=$mysql_monarch_database;host=$mysql_host";
my $dbn = DBI->connect($dbnotify, $mysql_username, $mysql_password);
my $dbm = DBI->connect($dbmonarch, $mysql_username, $mysql_password);

my $qn;
my $qni;
my $qm;
my $ref;

if (defined($datetime)) {
	$qn = $dbn->prepare("SELECT datetime FROM schedules WHERE team = '$team' AND datetime <= '$datetime' ORDER BY datetime DESC LIMIT 1")
	  or die "Cannot prepare mysql query for DB ($mysql_onduty_database): $dbn->errstr()";
} else {
	$qn = $dbn->prepare("SELECT datetime FROM schedules WHERE team = '$team' AND datetime <= NOW() ORDER BY datetime DESC LIMIT 1")
	  or die "Cannot prepare mysql query for DB ($mysql_onduty_database): $dbn->errstr()";
}

$qn->execute()
        or die "Cannot query mysql in DB ($mysql_onduty_database): $dbn->errstr()";

if ($qn->rows < 1) {
	if (defined($defaultpager)) {
		send_notification($team, undef, $defaultpager, $notifycmd, $message);
		if ($opt_verbose) {
			print "WARNING: Cannot find any entry in schedules table, notification sent to defaultpager ($defaultpager)!\n";
		}
		exit 0;
	}
	if ($opt_verbose) {
		print "ERROR: Cannot find any entry in schedules table, no notification sent!\n";
	}
	exit 1;
}

($datetime) = $qn->fetchrow_array();

#
# Now search all entries with this datetime and this team
#

$qn = $dbn->prepare("SELECT * FROM schedules WHERE team = '$team' AND datetime = '$datetime' ORDER BY contact_id")
  or die "Cannot prepare mysql query for DB ($mysql_onduty_database): $dbn->errstr()";
$qn->execute()
  or die "Cannot query mysql in DB ($mysql_onduty_database): $dbn->errstr()";

my $contact_id;
my $pager;
while ($ref = $qn->fetchrow_hashref()) {
        if (@opt_debug) {
                printf "%s\n", Data::Dumper::Dumper($ref);
        }
        $contact_id = $ref->{'contact_id'};
	send_notification($team, $contact_id, $defaultpager, $notifycmd, $message);
}

exit 0;
#
# FUNKTIONS
#
sub send_notification() {
	my ($team, $contact_id, $defaultpager, $sendcmd, $message) = @_;
	my $prefix = "";
	my $sending = 0;

	if (defined($contact_id)) {
		$qm = $dbm->prepare("SELECT pager FROM contacts WHERE contact_id = '$contact_id'")
		  or die "Cannot prepare mysql query for DB ($mysql_onduty_database): $dbm->errstr()";
		$qm->execute()
		  or die "Cannot query mysql in DB ($mysql_onduty_database): $dbm->errstr()";
		if ($qm->rows > 0) {
			($pager) = $qm->fetchrow_array();
			$sending = 1;
		} elsif (defined($defaultpager)) {
			$pager = $defaultpager;
			$prefix = "CONTACT_ID($contact_id) NOT FOUND: ";
			$contact_id = 9999;
			$sending = 1;
			if ($opt_verbose) {
				print "Notifying to Default Pager ($pager), contact_id ($contact_id) not found in DB\n";
			}
		}
	} elsif (defined($defaultpager)) {
		$pager = $defaultpager;
		$contact_id = 9999;
		$sending = 1;
		if ($opt_verbose) {
			print "Notifying to Default Pager ($pager)\n";
		}
	}
	if (!$sending) {
		if (defined($opt_testonly)) {
			print "NOT notifying anything, as contact_id could not be found and no defaultpager\n";
			return;
		}
		$qni = $dbn->prepare("INSERT INTO notify_log (timestamp, team, contact_id, message) VALUES (NOW(), '$team', '0', '$message')")
	          or die "Cannot prepare mysql insert for DB ($mysql_onduty_database): $dbn->errstr()";
		$qni->execute()
	          or die "Cannot insert into mysql in DB ($mysql_onduty_database): $dbn->errstr()";
		return;
	}
	if (defined($opt_testonly)) {
		print "Notifying Contact ($contact_id) on Pager ($pager)\n";
		return;
	}
	$sendcmd =~ s/\@CONTACT\@/$pager/g;
	$sendcmd =~ s/\@MESSAGE\@/$message/g;
	`$sendcmd`;
	$qni = $dbn->prepare("INSERT INTO notify_log (timestamp, team, contact_id, message) VALUES (NOW(), '$team', '$contact_id', '$prefix$message')")
          or die "Cannot prepare mysql insert for DB ($mysql_onduty_database): $dbn->errstr()";
	$qni->execute()
          or die "Cannot insert into mysql in DB ($mysql_onduty_database): $dbn->errstr()";
}

sub print_help() {
        printf "%s, Version %s\n",$PROGNAME, $VERSION;
        print "Copyright (c) 2018 Juergen Vigna <juergen.vigna\@wuerth-phoenix.com>\n";
        print "This program is licensed under the terms of the\n";
        print "GNU General Public License\n(check source code for details)\n";
        print "\n";
        printf "Script to send notifications over a schedule table in MySQL\n";
        print "\n";
        print_usage();
        print "\n";
        print " -V (--version)      Plugin version\n";
        print " -h (--help)         usage help\n";
        print " --test              Test Mode only, do not send anything\n";
		print " -T (--team)         Name of team to send this notification (required)\n";
		print " -P (--defaultpager) Default Pager to send notification to if team or contact_id could not be found\n";
		print " -M (--message)      Text of message to send for this notification (required)\n";
		print " -d (--datetime)     MySQL Datetime to send this notification for (default: NOW())\n";
		print " -N (--notifycmd)    The command to use for this notification\n";
		print "                     Use this 2 macros inside the command:\n";
		print "                     \@CONTACT\@: Is substituted by the actual contact(s)\n";
		print "                     \@MESSAGE\@: Is substituted by your message\n";
		print "                     default: $notifycmd\n";
        print "\n";
        print " MySQL Integration:\n";
        print " --mysql_onduty_database mysql database name (default $mysql_onduty_database)\n";
        print " --mysql_username        mysql database username (default $mysql_username)\n";
        print " --mysql_password        mysql database password (default ******)\n";
        print " --mysql_host            mysql database hostname/ip (default $mysql_host)\n";
        print "\n";
}

sub print_usage() {
        print "Usage: \n";
        print "  $PROGNAME -T|--team <teamname> -M <message> [-P <defaultpager>] [-d <datetime>] [-N <nofifycmd>] [--mysql_onduty_database <databasename>] [--mysql_username <username>] [--mysql_password <password>] [--mysql_host <host/ip>]\n";
        print "  $PROGNAME [-h | --help]\n";
        print "  $PROGNAME [-V | --version]\n";
}

