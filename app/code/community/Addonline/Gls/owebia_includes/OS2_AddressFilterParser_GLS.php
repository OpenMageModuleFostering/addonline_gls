<?php

/**
 * Copyright (c) 2008-13 Owebia
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @website	http://www.owebia.com/
 * @project	Magento Owebia Shipping 2 module
 * @author	 Antoine Lemoine
 * @license	http://www.opensource.org/licenses/MIT  The MIT License (MIT)
**/

class OS2_AddressFilterParser_GLS
{
	protected $input = null;
	protected $position = null;
	protected $buffer_start = null;

	protected $output = '';
	protected $level = null;
	protected $parent_level = null;
	protected $regexp = false;
	protected $case_insensitive = false;

	public function parse($input) {
		$this->current = array();

		$this->input = $input;
		$this->length = strlen($this->input);
		// look at each character
		$join = ' && ';
		for ($this->position=0; $this->position < $this->length; $this->position++) {
			switch ($this->input[$this->position]) {
				case ')':
					if ($this->regexp) break;
					$this->push($this->buffer().')');
					$this->parent_level = null;
					break;
				case ' ':
					if ($this->regexp) break;
					$this->push($this->buffer());
					break;
				case '-':
					if ($this->regexp) break;
					$this->push($this->buffer());
					$join = ' && !';
					break;
				case ',':
					if ($this->regexp) break;
					$this->push($this->buffer());
					$this->push(' || ');
					break;
				case '(':
					if ($this->regexp) break;
					$this->push($this->buffer());
					$this->push($join, $only_if_not_empty = true);
					$this->push('(');
					$this->parent_level = $this->level;
					$join = ' && ';
					break;
				case '/':
					$this->regexp = !$this->regexp;
				default:
					if ($this->buffer_start === null) {
						$this->buffer_start = $this->position;
					}
			}
		}
		$this->push($this->buffer());
		return $this->output;
	}

	protected function buffer() {
		if ($this->buffer_start !== null) {
			// extract string from buffer start to current position
			$buffer = substr($this->input, $this->buffer_start, $this->position - $this->buffer_start);
			// clean buffer
			$this->buffer_start = null;
			// throw token into current scope

			if ($buffer=='*') {
				$buffer = 1;
			} else if ($this->parent_level=='country') {
				if (preg_match('/^[A-Z]{2}$/', $buffer)) {
					$buffer = "{{c}}==='{$buffer}'";
					$this->level = 'country';
				} else if (substr($buffer, 0, 1)=='/' && (substr($buffer, strlen($buffer)-1, 1)=='/' || substr($buffer, strlen($buffer)-2, 2)=='/i')) {
					$case_insensitive = substr($buffer, strlen($buffer)-2, 2)=='/i';
					$buffer = "preg_match('".str_replace("'", "\\'", $buffer)."', '{p}')";
				} else if (strpos($buffer, '*')!==false) {
					$buffer = "preg_match('/^".str_replace(array("'", '*'), array("\\'", '(?:.*)'), $buffer)."$/', '{p}')";
				} else {
					$buffer = "({{p}}==='{$buffer}' || {{r}}==='{$buffer}')";
				}
			} else if (preg_match('/^[A-Z]{2}$/', $buffer)) {
				$buffer = "{{c}}==='{$buffer}'";
				$this->level = 'country';
			}
			return $buffer;
		}
		return null;
	}

	protected function push($text, $only_if_not_empty = false) {
		if (isset($text)) {
			if (!$only_if_not_empty || $this->output) $this->output .= $text;
			//echo "\"$this->output\"<br/>";
		}
	}
}
