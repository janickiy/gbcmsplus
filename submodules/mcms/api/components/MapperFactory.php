<?php

namespace mcms\api\components;


use InvalidArgumentException;
use mcms\api\mappers\CountriesMapper;
use mcms\api\mappers\LandingCategoriesMapper;
use mcms\api\mappers\LandingPayTypesMapper;
use mcms\api\mappers\LandingsMapper;
use mcms\api\mappers\OfferCategoriesMapper;
use mcms\api\mappers\OperatorsMapper;
use mcms\api\mappers\PartnersMapper;
use mcms\api\mappers\PlatformsMapper;
use mcms\api\mappers\ProvidersMapper;
use mcms\api\mappers\SourcesMapper;
use mcms\api\mappers\StreamsMapper;
use Yii;

/**
 * Class MapperFactory
 */
final class MapperFactory
{
    /**
     * @param string $type
     * @param array $params
     * @return BaseMapper
     * @throws InvalidArgumentException
     */
    public static function factory($type, $params = [])
    {
        switch ($type) {
            case PartnersMapper::getName():
                return Yii::createObject(PartnersMapper::class, [$params]);
            case CountriesMapper::getName():
                return Yii::createObject(CountriesMapper::class, [$params]);
            case OperatorsMapper::getName():
                return Yii::createObject(OperatorsMapper::class, [$params]);
            case SourcesMapper::getName():
                return Yii::createObject(SourcesMapper::class, [$params]);
            case LandingsMapper::getName():
                return Yii::createObject(LandingsMapper::class, [$params]);
            case LandingPayTypesMapper::getName():
                return Yii::createObject(LandingPayTypesMapper::class, [$params]);
            case ProvidersMapper::getName():
                return Yii::createObject(ProvidersMapper::class, [$params]);
            case StreamsMapper::getName():
                return Yii::createObject(StreamsMapper::class, [$params]);
            case PlatformsMapper::getName():
                return Yii::createObject(PlatformsMapper::class, [$params]);
            case LandingCategoriesMapper::getName():
                return Yii::createObject(LandingCategoriesMapper::class, [$params]);
            case OfferCategoriesMapper::getName():
                return Yii::createObject(OfferCategoriesMapper::class, [$params]);
        };

        throw new InvalidArgumentException("Unknown mapper type '$type'");
    }
}