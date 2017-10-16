<?php

namespace Silver;


use DateTime;
use Exception;
use League\Csv\Reader;
use League\Csv\Writer;

class Engine
{
    /**
     * @var array
     */
    protected $args;

    /**
     * @var array
     */
     protected $errors;

    /**
     * Engine constructor.
     * @param array $args
     */
    public function __construct(array $args)
    {
        $this->args = $args;
        $this->errors = [];
    }

    public function execute()
    {
        $reader = Reader::createFromPath($this->args['f']);
        $reader->setDelimiter(';');
        $count = $reader->each(function() {
            return true;
        });

        $builder = new Builder($count);
        $dispatcher = new Dispatcher($this->args['h']);

        foreach ($reader->fetchAssoc(0) as $row) {
            $builder->progress();

            try {
                $supplier = $builder->payload($row);
                $id = $dispatcher->send($supplier);

                $owner = $builder->ownerApprovals($supplier);
                $dispatcher->ownerApproval($owner, $id);

                $supplier = $builder->supplierApprovals($supplier);
                $dispatcher->esApproval($supplier, $id);

                $this->errors[] = $row;
            }
            catch(Exception $e) {
                //$row['message'] = $e->getMessage();
                //$this->errors[] = $row;
            }
        }

        echo PHP_EOL.'Total errors: '.count($this->errors).PHP_EOL;

        if(count($this->errors) > 0) {
            $this->csvErrors();
        }
    }

    private function csvErrors()
    {
        $rows = array_map(function($value) {
            return array_values($value);
        }, $this->errors);

        $csvErrors = sprintf('log/%s.csv', (new DateTime())->format('His'));
        $writer = Writer::createFromPath($csvErrors, 'w+');
        $writer->setDelimiter(';');
        $writer->insertOne(array_keys($this->errors[0]));
        $writer->insertAll($rows);
    }
}