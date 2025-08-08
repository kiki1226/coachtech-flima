<?php

use Tests\TestCase;

// Feature / Unit のテストは Laravel の TestCase で起動する
uses(TestCase::class)->in('Feature', 'Unit');
