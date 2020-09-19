<?php

namespace Cashbox\BoxBundle\Model\Till;

class TillMessages
{
    const MSG_CASHBOX_UNAV = "Касса не доступна";
    const MSG_ERROR = "Ошибка";
    const MSG_ERROR_HASH = "Ошибка в контрольнной сумме";
    const MSG_ERROR_TIN = "Неправильно выбрана организация";
    const MSG_ERROR_CHECK = "Чек уже был пробит ранее";
}