<?php
namespace BWTV\ForTheAPIs\Enums;

class ResponseCode
{
    ###
    #  Methods:
    ###
    const METHOD_NONE = '00';
    const METHOD_TOAST = '01';
    const METHOD_CONFIRM = '02';

    # 成功
    const SUCCESS = '0000';

    # 表單類錯誤
    const VALIDATION_ERROR = '9700';

    # Client端輸入錯誤
    const BAD_REQUEST = '9800';
    const UNAUTHENTICATED = '9801';
    const TOO_MANY_REQUEST = '9829';
    const PERMISSION_DENY = '9803';
    const NOT_EXIST = '9804';
    const ENTITY_NOT_EXIST = '9804';
    const ROUTE_NOT_EXIST = '9805';

    # Server端錯誤
    const SERVER_ERROR = '9900';
    const UNDER_MAINTAINACE = '9903';

    # for develop
    const MISSING_FILES = '9904';
    const DB_ERROR = '9922';
    const CACHE_ERROR = '9963';
}
