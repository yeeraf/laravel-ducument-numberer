<?php

namespace Yeeraf\DocumentNumberer;


use Yeeraf\DocumentNumberer\Models\DocumentNumber;

/**
 * Generate Running Number
 * $prefix 2 digit of year and month, example 2101
 * $suffix -
 * $padLength 6
 * $padString 0
 * $padType string left
 * $autoExtend bool true
 */
class DocumentNumberer
{
    protected $prefix;

    protected $name;
    protected $suffix;
    protected $padString;
    protected $padLength;
    protected $padType;
    protected $teamId;
    protected $autoExtend;

    public function __construct()
    {
        $this->prefix = \Carbon\Carbon::today()->format("ym");
        $this->name = "";
        $this->suffix = "";
        $this->padLength = 6;
        $this->padString = "0";
        $this->padType = "left";
        $this->autoExtend = true;
        $this->teamId = "";
    }

    /**
     *
     */
    public function generate()
    {
        $dn = $this->getCurrentDocumentNumber();

        return  $dn->prefix . $this->createRunningNumber($dn) . $dn->suffix;
    }

    public function getCurrentDocumentNumber(): DocumentNumber
    {
        $dn = DocumentNumber::firstOrCreate(
            [
                "name" => $this->name,
                "prefix" => $this->prefix,
                "suffix" => $this->suffix,
                "pad_length" => $this->padLength,
                "pad_string" => $this->padString,
                "pad_type" => $this->padType === "right" ? "right" : 'left',
                "team_id" => $this->teamId,
            ],
            [
                "current_number" => 0,
            ]
        );
        $dn->increment('current_number');

        return $dn;
    }


    public  function createRunningNumber($dn): string
    {
        $padType =  $dn->pad_type === "right" ? STR_PAD_RIGHT : STR_PAD_LEFT;

        $generated = str_pad(
            $dn->current_number,
            $dn->pad_length,
            $dn->pad_string,
            $padType
        );

        if (!$this->autoExtend &&  strlen($generated) > (int) $dn->pad_length) {
            // dd(strlen($generated), (int) $dn->pad_length);
            throw new \Exception("running number length go over pad length", 1);
        }

        return $generated;
    }

    public function name(string $name): self
    {
        $this->name = $name ?? $this->name;

        return $this;
    }

    public function prefix(string $prefix): self
    {
        $this->prefix = $prefix ?? $this->prefix;

        return $this;
    }

    public function suffix(string $suffix): self
    {
        $this->suffix = $suffix ?? $this->suffix;

        return $this;
    }

    public function padLength(string $padLength): self
    {
        $this->padLength = $padLength ?? $this->padLength;

        return $this;
    }

    public function padString(string $padString): self
    {
        $this->padString = $padString ?? $this->padString;

        return $this;
    }

    public function padType(string $padType)
    {
        $this->padType = $padType  == 'right' ? 'right' : 'left';

        return $this;
    }

    public function teamId(string|int $teamId): self
    {
        $this->teamId = $teamId;

        return $this;
    }

    public function autoExtend(bool $autoExtend = true): self
    {
        $this->autoExtend = $autoExtend === true ? true : false;

        return $this;
    }
}
