@extends('main.template')
@if (Auth::user()->role == "student" && Request::is('home'))
@include('main.home.home_student')
@endif

@if (Auth::user()->role == "mentor" && Request::is('home'))
@include('main.home.home_mentor')
@endif

@if (Auth::user()->role == "superadmin" && Request::is('home'))
@include('main.home.home_superadmin')
@endif
