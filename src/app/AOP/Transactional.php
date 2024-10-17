<?php 

namespace App\AOP;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Transactional
{
}