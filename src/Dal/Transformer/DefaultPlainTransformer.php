<?php
namespace App\Dal\Transformer;

use App\Dal\Dtos\RefreshTokenDto;
use App\Dal\Dtos\UserDto;
use App\Dal\Models\RefreshToken;
use App\Dal\Models\User;
use App\Http\Exceptions\InternalServerErrorException;
use App\Utils\Converters;

class DefaultPlainTransformer implements PlainTransformer
{
    #[\Override]
    public function toUser(array $plain): UserDto {
        $model = static::convertOrError($plain, User::class, [
            'createdAt' => static::datetimeGetter(),
            'updatedAt' => static::datetimeGetter(),
        ]);
        return Converters::instanceToObject($model, UserDto::class);
    }

    #[\Override]
    public function toRefreshToken(array $plain): RefreshTokenDto {
        $model = static::convertOrError($plain, RefreshToken::class, [
            'issuedAt' => static::datetimeGetter(),
            'expiresAt' => static::datetimeGetter(),
        ]);
        return Converters::instanceToObject($model, RefreshTokenDto::class);
    }

    private static function convertOrError(
        array $plain,
        string $modelClass,
        array $valueGetters = null,
        array $keyMappers = null): object {
        $model = Converters::sqlAssocArrayToModel($plain, $modelClass, $valueGetters, $keyMappers);
        if (!$model) {
            throw new InternalServerErrorException();
        }
        return $model;
    }

    private static function datetimeGetter() {
        return fn (?string $value) => $value !== null ? new \DateTimeImmutable($value) : null;
    }
}
