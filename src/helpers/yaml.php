<?php

if (!function_exists('yaml')) {


    /**
     *
     * @param string $data - If file exists, return file read and  parsed as yaml. If file dont exists but contain something, parse as yaml,
     * @return void
     */
    function yaml(string $data = "", ?string $yaml = "")
    {

        if (is_string($data) && file_exists($data) && is_readable($data)) {
            if (filesize($data) > ini_get('MEMORY_LIMIT')) {
                error_log("Likeley this will crash with memory issues, input file to big.");

            }
            #Process::Termin
            if (strpos($data,)) {
                $data = file_get_contents($data);
            }
        }


        return [];
    }

    class yaml
    {
        public function __construct(public ?string $data = "", public string $infile, public string $outfile)
        {

        }
    }
}
