#!/usr/bin/perl
use strict;
use LWP::UserAgent;
use HTTP::Date;
use XML::LibXML;

my $oOutput = XML::LibXML->createDocument;

my $iMaxLines = 2000;
my $iLineNo = 0;
my $oEle;
my $strURL;
my $file;

print "$#ARGV\n";
if ($#ARGV == 1) {
	$file = shift;
	$strURL = shift;
	open FILE, $file or die $!;
	$oEle = $oOutput->createComment("Reading " . $file);
} elsif ($#ARGV == 0) {
	$strURL = shift;
	open FILE, '-' or die $!;
	$oEle = $oOutput->createComment("Reading STDIN");
} else { 
	print("Usage:  " . $0 . " [<file>] http://<server>[[:<port>]/<path>]/\n\n");
	print("\t$0 reads data from a file or standard input, and sends it\n" .
		"\tto the Tapestry web server via HTTP.\n\n");
	exit(1);
}
	
	$oEle = $oOutput->appendChild($oEle);
		
	my $oHT = $oOutput->createElement("Timeline");
	$oOutput->setDocumentElement($oHT);
	
	my $oDat = $oOutput->createElement("Data");
	$oDat = $oHT->appendChild($oDat);
	
	while(<>) {
		#print "$_\n";
		$oDat->appendChild($oOutput->createCDATASection($_));
		$iLineNo += 1;
		if ($iLineNo >= $iMaxLines) {
			$iLineNo = 0;		
			dispatch($oOutput, $strURL);
			$oHT->removeChild($oDat);
			$oDat = $oOutput->createElement("Data");
			$oDat = $oHT->appendChild($oDat);
		}
	}
	dispatch($oOutput, $strURL);

sub dispatch {
	my $oOutput = shift; 
	my $strURL = shift;
	if ($oOutput) {
		print "[  ] " . time2str() . " sending to " . $strURL . "\n";
		# print $oOutput->toString(1) . "\n";
  		my $ua = LWP::UserAgent->new;
  		$ua->agent("Tapestry/0.1 ");
  		
  		# Create a request
		my $req = HTTP::Request->new(POST => $strURL);
		$req->content_type('text/xml');
		$req->content($oOutput->toString(1));
		
		# Pass request to the user agent and get a response back
		my $res = $ua->request($req);
		
		# Check the outcome of the response
		if ($res->is_success) {
			print $res->content . "\n";
			print "[" . $res->message . "] " . time2str() . "\n";
		} else {
			print $res->status_line, "\n";
		}
	}
}
