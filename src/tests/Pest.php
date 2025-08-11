<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

// これで Feature/ 配下のテストはすべて自動でマイグレーション実行
uses(RefreshDatabase::class)->in('Feature');
