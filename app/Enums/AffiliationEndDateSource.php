<?php

namespace App\Enums;

/**
 * Who recorded an Affiliation's end date. When the two disagree, the Organisation's date wins.
 */
enum AffiliationEndDateSource: string
{
    case User = 'user';
    case Organisation = 'organisation';
}
