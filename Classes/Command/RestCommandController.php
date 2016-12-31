<?php

namespace Cundd\Rest\Command;

use Cundd\CunddComposer\Autoloader;
use Cundd\Rest\Domain\Model\Document;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
} else {
    Autoloader::register();
}

/*
 *  Copyright notice
 *
 *  (c) 2014 Daniel Corn <info@cundd.net>, cundd
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

class RestCommandController extends CommandController
{
    /**
     * ASCII command escape
     */
    const ESCAPE = "\033";

    /**
     * ASCII style normal
     */
    const NORMAL = "[0m";

    /**
     * ASCII color green
     */
    const GREEN = "[0;32m";

    /**
     * ASCII color red
     */
    const RED = "[0;31m";

    /**
     * Document repository
     *
     * @var \Cundd\Rest\Domain\Repository\DocumentRepository
     * @inject
     */
    protected $documentRepository;

    /**
     * List all Documents
     *
     * @param string $database Name of the database to list
     * @param bool   $full     Display the Documents body
     */
    public function showDocumentsCommand($database = '', $full = false)
    {
        if ($database) {
            $documents = $this->documentRepository->findByDatabase($database);
        } else {
            $documents = $this->documentRepository->findAllIgnoreDatabase();
        }
        if ($documents) {
            foreach ($documents as $document) {
                $this->showDocument($document, $full);
            }
        } else {
            $this->outputLine(
                $database ? sprintf('No documents found in database "%s"', $database) : 'No documents found'
            );
        }
    }

    /**
     * Remove all Documents from the given database
     *
     * @param string $database Name of the database to remove
     */
    public function removeDatabaseCommand($database)
    {
        $this->documentRepository->setDatabase($database);
        $count = $this->documentRepository->countAll();

        if ($count == 0) {
            $this->outputLine('Database "' . $database . '" is empty');

            return;
        }


        // Ask before deleting
        $prompt = 'Remove ' . $count . ' documents from database "' . $database . '" [yn]?';
        if (function_exists('readline')) {
            $choice = readline($prompt);
        } else {
            echo $prompt . ' ';
            $choice = stream_get_line(STDIN, 1024, PHP_EOL);
        }

        if ($choice === 'y') {
            $this->outputLine(
                ''
                . static::ESCAPE . static::RED
                . 'Deleting ' . $count . ' documents'
                . static::ESCAPE . static::NORMAL
            );
            $this->documentRepository->removeAllFromDatabase($database);
        } else {
            $this->outputLine('Nothing deleted');
        }
    }

    /**
     * Displays information about the given Document
     *
     * @param Document $document
     * @param bool     $showBody
     */
    public function showDocument(Document $document, $showBody = false)
    {
        $this->outputLine(
            static::ESCAPE . static::GREEN
            . 'Database: ' . $document->_getDb() . ' '
            . 'ID: ' . ($document->getId() ? $document->getId() : '(Missing ID)') . ' '
            . static::ESCAPE . static::NORMAL
        );

        if ($showBody) {
            $this->outputLine(
                $this->formatJsonData($document->_getDataProtected(), true) . PHP_EOL
            );
        }
    }

    /**
     * Returns a formatted json-encoded version of the given data
     *
     * @param mixed $data         The data to format
     * @param bool  $isJsonString Set this to TRUE if the given data already is a JSON string
     * @return string
     */
    public function formatJsonData($data, $isJsonString = false)
    {
        if ($isJsonString) {
            $data = json_decode($data, true);
        }

        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
