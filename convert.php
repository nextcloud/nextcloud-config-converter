<?php

/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 Morris Jobke <hey@morrisjobke.de>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 * This code extracts the code comments out of Nextcloud's
 * config/config.sample.php and creates an RST document
 */


require 'vendor/autoload.php';

function escapeRST($string) {
	# just replace all \ by \\ if there is no code block present
	if(strpos($string, '``') === false) {
		return str_replace('\\', '\\\\', $string);
	}

	$parts = explode('``', $string);
	foreach ($parts as $key => &$part) {
		# just even parts are outside the code block
		# example:
		#
		# 	Test code: ``$my = $code + 5;`` shows that ...
		#
		# The code part has the id 1 and is an odd number
		if($key%2 == 0) {
			str_replace('\\', '\\\\', $part);
		}
	}

	return implode('``', $parts);
}

// tag which invokes to copy a config description to the current position
$COPY_TAG = 'see';
// file which should be parsed
$CONFIG_SAMPLE_FILE = $argv[1] ?? '../server/config/config.sample.php';
// config documentation file to put content in
$OUTPUT_FILE = $argv[2] ?? 'admin_manual/configuration/config_sample_php_parameters.rst';

/**
 * h - help
 * i - input file
 * o - output file
 * t - tag
 */
$options = getopt(
	'ht::i::o::',
	array('help', 'input-file::', 'output-file::', 'tag::'));

if(array_key_exists('h', $options) || array_key_exists('help', $options)) {
	$helptext = $argv[0] . " [OPTION] ... (all options are optional)\n\n" .
	" -h, --help                   Print this help text\n".
	" -iFILE, --input-file=FILE    Specify the input file (Default: ../server/config/config.sample.php)\n".
	" -oFILE, --output-file=FILE   Specify the output file (Default: admin_manual/configuration/config_sample_php_parameters.rst)\n".
	" -tNAME, --tag=NAME           Tag to use for copying a config entry (default: see)\n".
	"\n";
	print($helptext);
	exit(0);
}

if(array_key_exists('t', $options)){
	$COPY_TAG = $options['t'];
} elseif (array_key_exists('tag', $options)) {
	$COPY_TAG = $options['tag'];
}

if(array_key_exists('i', $options)){
	$CONFIG_SAMPLE_FILE = $options['i'];
} elseif (array_key_exists('input-file', $options)) {
	$CONFIG_SAMPLE_FILE = $options['input-file'];
}

if(array_key_exists('o', $options)){
	$OUTPUT_FILE = $options['o'];
} elseif (array_key_exists('output-file', $options)) {
	$OUTPUT_FILE = $options['output-file'];
}

// read file
$docBlock = file_get_contents($CONFIG_SAMPLE_FILE);

// trim everything before this (including itself)
$start = '$CONFIG = array(';
if (strpos($docBlock, $start) === false) {
	$start = '$CONFIG = [';
	if (strpos($docBlock, $start) === false) {
		print("Could not find head of config array in config.sample.php\n");
		exit(1);
	}
}
$docBlock = substr($docBlock, strpos($docBlock, $start) + strlen($start));

// trim the end of the config variable
$end = ');';
if (strrpos($docBlock, $end) === false) {
	$end = '];';
	if (strpos($docBlock, $end) === false) {
		print("Could not find tail of config array in config.sample.php\n");
		exit(1);
	}
}
$docBlock = substr($docBlock, 0, strrpos($docBlock, $end));

// split on '/**'
$blocks = explode('/**', $docBlock);

// output that gets written to the file
$output = '';
$outputFirstParagraph = '';
// array that holds all RST representations of all config options to copy them
$lookup = array();

// check if the current processed block is the first section (first call sets
// this to true and all other sections to false)
$isFirstSection = null;

foreach ($blocks as $block) {
	if (trim($block) === '') {
		continue;
	}
	$block = '/**' . $block;
	$parts = explode(' */', $block);
	$id = null;
	$doc = '';
	$code = '';
	// there should be exactly two parts after the split - otherwise there are
	// some mistakes in the parsed block
	if (count($parts) !== 2) {
		echo '<h3>Uncommon part count!</h3><pre>';
		print_r($parts);
		echo '</pre>';
	} else {
		$doc = $parts[0] . ' */';
		$code = $parts[1];
	}

	// this checks if there is a config option below the comment (should be one
	// if there is a config option or none if the comment is just a heading of
	// the next section
	preg_match('!^\'([^\']*)\'!m', $block, $matches);
	if (!in_array(count($matches), array(0, 2))) {
		echo 'Uncommon matches count<pre>';
		print_r($matches);
		echo '</pre>';
	}

	// if there are two matches a config option was found -> set it as ID
	if (count($matches) === 2) {
		$id = $matches[1];
	}

	// parse the doc block
	$factory  = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
	$phpdoc = $factory->create($doc);

	// check for tagged elements to replace the tag with the actual config
	// description
	$references = $phpdoc->getTagsByName($COPY_TAG);
	if (!empty($references)) {
		foreach ($references as $reference) {
			$name = $reference->getName();
			if (array_key_exists($name, $lookup)) {
				// append the element at the current position
				$output .= $lookup[$name];
			}
		}
	}

	$RSTRepresentation = '';

	// generate RST output
	if (is_null($id)) {
		// print heading - no
		$heading = $phpdoc->getSummary();
		$RSTRepresentation .= "\n" . $heading . "\n";
		$RSTRepresentation .= str_repeat('-', strlen($heading)) . "\n\n";
		$longDescription = (string) $phpdoc->getDescription();
		if (trim($longDescription) !== '') {
			$RSTRepresentation .= $longDescription . "\n\n";
		}
		if($isFirstSection === null) {
			$isFirstSection = true;
		} else {
			$isFirstSection = false;
		}
	} else {
		$RSTRepresentation .= "\n" . $id . "\n";
		$RSTRepresentation .= str_repeat('^', strlen($id)) . "\n\n";

		// mark as literal (code block)
		$RSTRepresentation .= "\n::\n\n";
		// trim whitespace
		$code = trim($code);
		// intend every line by a tab - also trim whitespace
		// (for example: empty lines at the end)
		foreach (explode("\n", trim($code)) as $line) {
			$RSTRepresentation .= "\t" . $line . "\n";
		}
		$RSTRepresentation .= "\n";

		$fullDocBlock = $phpdoc->getSummary();
		$longDescription = $phpdoc->getDescription()->render();
		if ($longDescription !== '') {
			$fullDocBlock .=  "\n\n" . $longDescription;
		}

		// print description
		$RSTRepresentation .= escapeRST($fullDocBlock);
		// empty line
		$RSTRepresentation .= "\n";

		$lookup[$id] = $RSTRepresentation;
	}

	if($isFirstSection) {
		$outputFirstParagraph .= $RSTRepresentation;
	} else {
		$output .= $RSTRepresentation;
	}
}

$configDocumentation = file_get_contents($OUTPUT_FILE);
$configDocumentationOutput = '';

$tmp = explode('DEFAULT_SECTION_START', $configDocumentation);
if(count($tmp) !== 2) {
	print("There are not exactly one DEFAULT_SECTION_START in the config documentation\n");
	exit(1);
}

$configDocumentationOutput .= $tmp[0];
// append start placeholder
$configDocumentationOutput .= "DEFAULT_SECTION_START\n\n";
// append first paragraph
$configDocumentationOutput .= $outputFirstParagraph;
// append end placeholder
$configDocumentationOutput .= "\n.. DEFAULT_SECTION_END";

$tmp = explode('DEFAULT_SECTION_END', $tmp[1]);
if(count($tmp) !== 2) {
	print("There are not exactly one DEFAULT_SECTION_END in the config documentation\n");
	exit(1);
}
// drop the first part (old generated documentation which should be overwritten
// by  this script) and just process
$tmp  = explode('ALL_OTHER_SECTIONS_START', $tmp[1]);
if(count($tmp) !== 2) {
	print("There are not exactly one ALL_OTHER_SECTIONS_START in the config documentation\n");
	exit(1);
}
// append middle part between DEFAULT_SECTION_END and ALL_OTHER_SECTIONS_START
$configDocumentationOutput .= $tmp[0];
// append start placeholder
$configDocumentationOutput .= "ALL_OTHER_SECTIONS_START\n\n";
// append rest of generated code
$configDocumentationOutput .= $output;

// drop the first part (old generated documentation which should be overwritten
// by  this script) and just process
$tmp  = explode('ALL_OTHER_SECTIONS_END', $tmp[1]);
if(count($tmp) !== 2) {
	print("There are not exactly one ALL_OTHER_SECTIONS_END in the config documentation\n");
	exit(1);
}
// append end placeholder
$configDocumentationOutput .= "\n.. ALL_OTHER_SECTIONS_END";

$configDocumentationOutput .= $tmp[1];

// write content to file
file_put_contents($OUTPUT_FILE, $configDocumentationOutput);
