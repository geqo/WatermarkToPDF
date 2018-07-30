<?php
/*
 *
 * Copyright © 2018 Alex White geqo.ru
 * Author: Alex White
 * All rights reserved
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

namespace Geqo;


use Geqo\Exceptions\ExecException;
use Geqo\Exceptions\FileNotFoundException;
use Geqo\Exceptions\NotWritableException;

class WatermarkToPDF
{
    /**
     * Absolute path to image
     * @var string
     */
    private $watermark;

    /**
     * Absolute path to pdf file
     * @var string
     */
    private $file;

    /**
     * Absolute path to save
     * @var string
     */
    private $output;

    /**
     * X and Y offset relative to position
     * @var string
     */
    private $offset = '+50+50';

    /**
     * @var string
     */
    private $locale = 'en_US.UTF-8';

    /**
     * WatermarkToPDF constructor.
     * @param $watermark string Path to image
     * @param $file string Path to pdf file
     * @param $output string Output file
     * @throws ExecException
     * @throws FileNotFoundException
     * @throws NotWritableException
     */
    public function __construct($watermark, $file, $output)
    {
        if (! file_exists($watermark)) {
            throw new FileNotFoundException('Image not found: `' . $watermark . '`');
        }

        $this->watermark = $watermark;

        if (! file_exists($file)) {
            throw new FileNotFoundException('PDF not found: `' . $file . '`');
        }

        $this->file = $file;
        $outputDir = dirname($output);

        if (! is_writable($outputDir) && ! @mkdir($outputDir, 0777, true)) {
            throw new NotWritableException('Directory is not writable: `' . $outputDir . '`');
        }

        $this->output = $output;

        if (! $this->ifConvertExists()) {
            throw new ExecException('ImageMagick convert not found');
        }
    }

    /**
     * Set like $wm->setOffset('+50', '-50')
     * @param $x
     * @param $y
     */
    public function setOffset($x, $y)
    {
        $this->offset = $x . $y;
    }

    /**
     * @return bool
     */
    private function ifConvertExists()
    {
        if (stristr(`type convert`, 'not found')) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     * @throws ExecException
     */
    public function execute()
    {
        setlocale(LC_CTYPE, $this->locale);

        $command = 'convert ' . escapeshellarg($this->watermark) . ' -transparent white miff:- ' .
            '| convert -density 100 ' . escapeshellarg($this->file) . ' null: - -gravity SouthEast ' .
            '-geometry ' . $this->offset . ' -quality 100 -compose multiply ' .
            '-layers composite ' . escapeshellarg($this->output) . ' 2>&1';

        exec($command, $output, $return);

        if ($return !== 0) {
            $_output = implode(' ', $output);
            throw new ExecException($_output);
        }

        return $output;
    }

}