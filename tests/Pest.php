<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

uses(TestCase::class)->in('Feature');
uses(RefreshDatabase::class, WithFaker::class)->in('Feature/AuthTest.php');