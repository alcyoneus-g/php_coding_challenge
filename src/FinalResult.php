<?php

class FinalResult {
    public function parseResultFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("File does not exist: " . $filePath);
        }

        $file = fopen($filePath, "r");
        if ($file === false) {
            throw new Exception("Failed to open file: " . $filePath);
        }

        $header = fgetcsv($file);
        if ($header === false) {
            throw new Exception("Invalid file format: " . $filePath);
        }

        $records = [];
        while (($row = fgetcsv($file)) !== false) {
            if (count($row) === 16) {
                $record = $this->parseRecord($row, $header);
                $records[] = $record;
            }
        }

        fclose($file);

        return [
            "filename" => basename($filePath),
            "failure_code" => $header[1],
            "failure_message" => $header[2],
            "records" => $records
        ];
    }

    private function parseRecord(array $row, array $header): array
    {
        $amount = $this->parseAmount($row[8], $header[0]);
        $bankAccountName = strtolower(str_replace(" ", "_", $row[7]));
        $bankAccountNumber = $this->parseBankAccountNumber($row[6]);
        $bankBranchCode = $this->parseBankBranchCode($row[2]);
        $endToEndId = $this->parseEndToEndId($row[10], $row[11]);

        return [
            "amount" => $amount,
            "bank_account_name" => $bankAccountName,
            "bank_account_number" => $bankAccountNumber,
            "bank_branch_code" => $bankBranchCode,
            "bank_code" => $row[0],
            "end_to_end_id" => $endToEndId,
        ];
    }

    private function parseAmount(string $amountValue, string $currency): array
    {
        $amount = (float) $amountValue;
        $subunits = (int) ($amount * 100);

        return [
            "currency" => $currency,
            "subunits" => $subunits
        ];
    }

    private function parseBankAccountNumber($value): string
    {
        if (empty($value) || $value === "0") {
            return "Bank account number missing";
        }

        return (string) $value;
    }

    private function parseBankBranchCode($value): string
    {
        if (empty($value)) {
            return "Bank branch code missing";
        }

        return $value;
    }

    private function parseEndToEndId($part1, $part2): string
    {
        if (empty($part1) && empty($part2)) {
            return "End to end id missing";
        }

        return $part1 . $part2;
    }
}
?>
