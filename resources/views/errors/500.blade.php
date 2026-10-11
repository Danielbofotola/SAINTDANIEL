@extends('errors.layout')

@section('code', '500')
@section('titre', 'Erreur interne du serveur')
@section('message', "Une erreur inattendue s’est produite lors du traitement de votre demande.")
@section('detail', "Si l’erreur était : « Data truncated for column… » sur une inscription, lancez la migration qui élargit le statut des inscriptions (php artisan migrate).")
