<?php

namespace App\Models\Traits;

trait PreventUpdate
{
    protected static bool $enableUpdate = false;

    public static function enableUpdate(?bool $enableUpdate = null)
    {
        return static::$enableUpdate = !!($enableUpdate ?? static::$enableUpdate);
    }

    protected static function bootPreventUpdate()
    {
        //Impede as ações de atualização do banco já que não iremos criar, atualizar ou deletar

        static::deleting(function ($model) {
            if (!static::$enableUpdate) {
                return false;
            }
        });

        static::updating(function ($model) {
            if (!static::$enableUpdate) {
                return false;
            }
        });

        static::creating(function ($model) {
            if (!static::$enableUpdate) {
                return false;
            }
        });
    }
}
