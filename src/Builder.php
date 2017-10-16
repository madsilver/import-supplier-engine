<?php

namespace Silver;

/**
 * Class Parser
 * @package Silver
 */
class Builder
{
    /**
     * @var int
     */
    protected $total;

    /**
     * @var int
     */
    private $done = 1;

    /**
     * @var time
     */
    private $startTime;

    /**
     * Builder constructor.
     * @param int $total
     */
    public function __construct(int $total)
    {
        $this->total = $total - 1;
        $this->startTime = time();
    }

    /**
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function payload(array $data) : array
    {
        if (empty($data['name'])) {
            throw new \Exception('Field name required');
        }

        $payload = [
            "name" => $data["name"],
            "description" => $data["name"],
            "company" => [
                "name" => $data["name"],
                "address" => $data["street"],
                "complement" => "test",
                "postal_code" => $this->filter($data["postal_code"]),
                "city" => $data["city"],
                "state" => $data["state"],
                "country" => $data["country"],
                "phone" => $this->filter($data["phone"]),
                "email" => $data["e-mail"],
                "cnpj" => $this->filter($data["cnpj"], "cnpj"),
                "customer_care" => [
                    "name" => $data["name"],
                    "address" => $data["street"],
                    "complement" => "test",
                    "postal_code" => $this->filter($data["postal_code"]),
                    "city" => $data["city"],
                    "state" => $data["state"],
                    "country" => $data["country"],
                    "phone" => $this->filter($data["phone"]),
                    "email" => $data["e-mail"]
                ]
            ]
        ];

        return $payload;
    }

    public function ownerApprovals(array $data) : array
    {
        $data['juridical_approval'] = true;
        $data['fiscal_approval'] = true;
        return $data;
    }

    public function supplierApprovals(array $data) : array
    {
        $data['commercial_approval'] = true;
        return $data;
    }

    private function filter(string $field, string $type = "standard")
    {
        $pattern = '/[^0-9]/';
        $f = preg_replace($pattern, "", $field);

        if($type == "cnpj") {
            $f = str_pad($f, 14, "0", STR_PAD_LEFT);
        }

        return $f;
    }

    /**
     * @param int $size
     */
    public function progress(int $size=50) {

        if($this->done > $this->total) return;

        $perc = (double)($this->done/$this->total);

        $bar = floor($perc*$size);

        $status_bar = "\r[";
        $status_bar.= str_repeat("=", $bar);
        if($bar<$size){
            $status_bar.= ">";
            $status_bar.= str_repeat(" ", $size - $bar);
        } else {
            $status_bar.="=";
        }

        $disp = number_format($perc*100, 0);

        $status_bar.="] $disp%  $this->done/$this->total";

        $now = time();
        $rate = ($now - $this->startTime)/$this->done;
        $left = $this->total - $this->done;
        $eta = round($rate * $left, 2);
        $elapsed = $now - $this->startTime;

        $status_bar.= " remaining: ".number_format($eta)." sec  elapsed: ".number_format($elapsed)." sec";

        $this->done++;

        echo "$status_bar  ";
    }

}