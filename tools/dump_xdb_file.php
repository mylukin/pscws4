<?php
// Dump the plain text dictionary from .xdb file used by SCWS
// Usage: php dump_xdb_file.php <xdb file> [output file]
// $Id: $

ini_set('memory_limit', '1024M');
set_time_limit(0);
if (!isset($_SERVER['argv'][1]) || !is_file($_SERVER['argv'][1]))
{
	echo "Usage: {$_SERVER['argv'][0]} <xdb file> [output file]\n";
	exit(0);
}

$output = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : 'php://stdout';
if (!($fd = @fopen($output, 'w')))
{
	echo "ERROR: can not open the output file: {$output}\n";
	exit(0);
}

require 'xdb.class.php';
$xdb = new XTreeDB;
if (!$xdb->Open($_SERVER['argv'][1]))
{
	fclose($fd);
	echo "ERROR: input file {$_SERVER['argv'][1]} maybe not a valid XDB file.\n";
	exit(0);
}

$line = "# WORD\tTF\tIDF\tATTR\n";
fwrite($fd, $line);
$xdb->Reset();
while ($tmp = $xdb->Next())
{
	if (strlen($tmp['value']) != 12) continue;
	$word = $tmp['key'];
	$data = unpack("ftf/fidf/Cflag/a3attr", $tmp['value']);
	if (!($data['flag'] & 0x01)) continue;

	$line = sprintf("%s\t%.2f\t%.2f\t%.2s\n", $word, $data['tf'], $data['idf'], $data['attr']);
	fwrite($fd, $line);
}
fclose($fd);
$xdb->Close();