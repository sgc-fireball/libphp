<?php

namespace HRDNS\Exception;

class HomeMaticIOException extends IOException
{

    const ERROR_PARSER_NOT_ENOUGH_INPUT = 1;

    const ERROR_GENERAL = -1;
    const ERROR_UNKNOWN_DEVICE = -2;
    const ERROR_UNKNOWN_PARAMSET = -3;
    const ERROR_INVALID_DEVICE_ADDRESS = -4;
    const ERROR_UNKNOWN_PARAMETER_OR_VALUE = -5;
    const ERROR_INVLAID_ARGUMENT = -6;
    const ERROR_DEIVCE_UNABLE_TO_UPDATE = -7;
    const ERROR_INVALID_DUTYCYCLE = -8;
    const ERROR_DEVICE_OUT_OF_DISTANCE = -9;

    public function __construct($message = '', $code = 0, \Throwable $previous = null)
    {
        $newMessage = '';
        $message = trim($message, "\r\n\t :");
        switch ($code) {
            case self::ERROR_GENERAL:
                $newMessage = 'Allgemeiner Fehler';
                break;
            case self::ERROR_UNKNOWN_DEVICE:
                $newMessage = 'Unbekanntes Gerät / unbekannter Kanal';
                break;
            case self::ERROR_UNKNOWN_PARAMSET:
                $newMessage = 'Unbekannter Paramset';
                break;
            case self::ERROR_INVALID_DEVICE_ADDRESS:
                $newMessage = 'Es wurde eine Geräteadresse erwartet';
                break;
            case self::ERROR_UNKNOWN_PARAMETER_OR_VALUE:
                $newMessage = 'Unbekannter Parameter oder Wert';
                break;
            case self::ERROR_INVLAID_ARGUMENT:
                $newMessage = 'Operation wird vom Parameter nicht unterstützt';
                break;
            case self::ERROR_DEIVCE_UNABLE_TO_UPDATE:
                $newMessage = 'Das Interface ist nicht in der Lage ein Update durchzuführen';
                break;
            case self::ERROR_INVALID_DUTYCYCLE:
                $newMessage = 'Es seht nicht genügend DutyCycle zur Verfügung ';
                break;
            case self::ERROR_DEVICE_OUT_OF_DISTANCE:
                $newMessage = 'Das Gerät ist nicht in Reichweite';
                break;
        }
        parent::__construct('Error['.$code.'] '.$newMessage.' ('.$message.')', $code, $previous);
    }

}
