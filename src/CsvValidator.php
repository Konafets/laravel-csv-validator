<?php namespace Konafets\CsvValidator;

use Illuminate\Support\Collection;
use Importer;

/**
 * Class CsvValidator
 *
 * @package Konafets\CsvValidator
 * @author Stefano Kowalke <info@arroba-it.de>
 */
class CsvValidator
{

    /** @var Collection $csvData */
    private $csvData;

    /** @var Collection $rules */
    private $rules;

    /** @var Collection $ruleHeadingKeys */
    private $rulesHeaderKeys;

    /** @var Collection $headingRow */
    private $headingRow;

    /** @var Collection $errors */
    private $errors;

    /**
     * The constructor
     */
    public function __construct()
    {
        $this->rulesHeaderKeys = collect();
        $this->csvData = collect();
        $this->rules = collect();
    }

    public function make(string $csvPath, array $rules, bool $hasHeader = false) : CsvValidator
    {
        $this->setRules(collect($rules));

        $excel = Importer::make('Excel');
        $excel->load($csvPath);
        $csvData = $excel->getCollection();

        $this->headingRow = $this->extractHeaderFromCsv($csvData);
        $csvData = $this->removeHeadingRowFromCsv($csvData);
        $newRules = collect();

        $this->rulesHeaderKeys->each(function ($headerKey) use ($newRules) {
            $keyIndex = $this->headingRow->search($headerKey);

            if ($keyIndex !== false) {
                $newRules[$keyIndex] = $this->rules[$headerKey];
            } else {
                throw new Exception(sprintf('"%s" not found.', $headerKey));
            }
        });

        $this->setRules($newRules);

        if ($csvData->isEmpty()) {
            throw new \Exception('Not data found in file.');
        }

        $newCsvData = [];
        $ruleKeys = $this->rules->keys();

        foreach ($csvData as $csvRowKey => $csvRowValues) {
            if (! empty(array_filter($csvRowValues))) {
                foreach ($ruleKeys as $ruleKey) {
                    $newCsvData[$csvRowKey][] = $csvRowValues[$ruleKey];
                }
            };
        }

        $this->csvData = collect($newCsvData);

        return $this;
    }

    public function validate() : bool 
    {
        $errors = collect();

        $this->csvData->each(function ($csvValues, $rowIndex) use ($errors) {
            $validator = \Validator::make(array_values($csvValues), $this->rules->all());

            if ($this->headingRow->isNotEmpty()) {
                $validator->setAttributeNames($this->headingRow->toArray());
            }

            if ($validator->fails()) {
                $errors[$rowIndex] = $validator->messages()->toArray();
            }
        });

        $this->errors = $errors;

        return $this->errors->isEmpty();
    }

    public function getErrors() : Collection
    {
        return $this->errors;
    }

    public function getData() : Collection
    {
        return $this->csvData;
    }

    private function setRules(Collection $rules)
    {
        $this->rules = $rules;
        $this->rulesHeaderKeys = $rules->keys();
    }

    private function extractHeaderFromCsv(Collection $csvData) : Collection
    {
        $result = collect();
        foreach ($csvData[0] as $headerItem) {
            $result->push($this->removeControlCharsFromString($headerItem));
        }

        return $result;
    }

    private function removeHeadingRowFromCsv(Collection $csvData) : Collection
    {
        unset($csvData[0]);

        return $csvData->values();
    }

    private function removeControlCharsFromString(string $string) : string
    {
        return preg_replace('/[\x00-\x1F\x7F]/', '', $string);
    }
}
