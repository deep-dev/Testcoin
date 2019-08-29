<?php
/**
 * a single transaction, that creates (“emits”) some coins or moves them between accounts
 */
class Transaction
{
    const MONEY_EMISSION = 0;
    const MONEY_TRANSFER = 1;

    private $Id;                // (integer) – unique transaction id
    private $Type;              /* (integer) - type of transaction:
                                 * 0 – money emission. It means the system creates new “coins” and puts
                                 *     them to the destination account of the transaction (“to” property)
                                 * 1 – money transfer. It means that within this transaction one account
                                 *     transfers money to some other account.
                                */
    private $From;              // (string) – source account name
    private $To;                // (string) – destination account name
    private $Amount;            // (integer) – transaction amount
    private $Signature;         /* (string) MD5 hex digest of the transaction fields. In order to
                                 * calculate the signature, you should convert all properties (id, type, from, to,
                                 * amount) to strings, and concatenate them using a semicolon (“:”) character.
                                 * In other words, the signature will be equal to
                                 * MD5(“id:type:from:to:amount”), for example: MD5(“1:1:bob:alice:1000”) =
                                 * “2a172cf33d3444b5df7615378e6640e0” – will be a valid signature of
                                 * “money transfer” transaction from “bob” to “alice” with id 1 and amount of 1000.
                                 */
    public function __construct()
    {

    }

    public function __destruct()
    {/*
        echo '<pre>';
        var_dump($this);
        echo '</pre>';*/
    }

    public function getId()
    {
        return $this->Id;
    }

    public function getType()
    {
        return $this->Type;
    }

    public function getFrom()
    {
        return $this->From;
    }

    public function getTo()
    {
        return $this->To;
    }

    public function getAmount()
    {
        return $this->Amount;
    }

    public function getSignature()
    {
        return $this->Signature;
    }

    /**
     * set 'id' property for transaction
     * @param $id type: integer
     */
    public function setId( $id )
    {
        $this->Id = $id;
    }

    /**
     * set 'type' property. Should check if given type isset 'id' property for transaction
     * within the allowed range or throw an exception otherwise. If type is
     * "emission", then "from" property should be set to null.
     * @param $type type: integer
     */
    public function setType( $type )
    {
        if ( $type < 0 || $type > 1 ) {
            throw new Exception('Error in type: Type transation not valid');
        }
        if ( $this->Type == self::MONEY_EMISSION ) {
            $this->From = null;
        }
        $this->Type = $type;
    }

    /**
     * Set 'from' property for transaction. The method should perform the
     * following checks:
     *  - if transaction 'type' is 'emission' – ignore the passed 'from' value
     * and set 'from' property to null
     *  - if passed 'from' account is null, is shorter than 2 characters or longer
     * than 10 characters – throw an exception
     * @param $from type: string
     * @throws \InvalidArgumentException
     */
    public function setFrom( $from )
    {
        if ($this->Type == self::MONEY_EMISSION) {
            $this->From = null;
        } elseif ( is_null($from) || mb_strlen($from) < 2 || mb_strlen($from) > 10 ) {
            throw new Exception('Error in from: It\'s missing, or is shorter than 2 characters or longer than 10 characters');
        } else {
            $this->From = $from;
        }
    }

    /**
     * Set 'to' property for transaction. The method should perform the following checks:
     * - if passed 'to' account is null, is shorter than 2 characters or longer
     * than 10 characters – throw an exception
     * - if 'to' account is the same as the 'from' account – throw an exception
     * @param $to type: string
     * @throws \InvalidArgumentException
     */
    public function setTo( $to )
    {
        if ( is_null($to) || mb_strlen($to) < 2 || mb_strlen($to) > 10) {
            throw new Exception('Error in to: It\'s missing, or is shorter than 2 characters or longer than 10 characters');
        } elseif ( $this->from == $to ) {
            throw new Exception('Error in to: Not alowed when to account equal from account');
        } else {
            $this->To = $to;
        }
    }

    /**
     * Set 'amount' property. The method should perform the following checks:
     * - if amount is less than zero – throw an exception
     * @param $amount type: integer
     * @throws \InvalidArgumentException
     */
    public function setAmount( $amount )
    {
        if ($amount < 0) {
            throw new Exception('Error in amount: amount is less than zero');
        } else {
            $this->Amount = $amount;
        }
    }

    /**
     * Set 'signature' property. The method should perform the following checks:
     * - if passed signature’s length is not equal to 32 characters – throw an exception
     */
    public function setSignature()
    {
        $signature = md5( $this->Id . ':' . $this->Type . ':' . $this->From . ':' . $this->To . ':' . $this->Amount );
        if ( mb_strlen($signature) != 32 ) {
            throw new Exception('Error in signature: signature’s length is not equal to 32 characters');
        }
        $this->Signature = $signature;
    }
}

/*
 * holds 1 or more valid transactions
 */
class Block
{
    private $Id;                            // type: integer – unique block id
    private $Transaction = array();         // type: array - a list of transactions within this block

    public function __construct()
    { }

    public function __destruct()
    {/*
        echo '<pre>';
        var_dump($this);
        echo '</pre>';*/
    }

    public function getId()
    {
        return $this->Id;
    }

    public function getTransactions()
    {
        return $this->Transaction;
    }

    /**
     * set block id
     */
    public function setId( $id )
    {
        $this->Id = $id;
    }

    /**
     * Validate passed transaction. The method should perform the following checks:
     * - check if transaction signature is valid - see the signature algorithm
     * description in the Transaction class overview above
     * The method should return:
     * - true – if the passed transaction is valid
     * - false – otherwise
     * @param $transaction type: Transaction
     * @return boolean
     */
    public function validateTransaction( $transaction )
    {
        $signature = md5($transaction->getId() . ':' . $transaction->getType() . ':' . $transaction->getFrom() . ':' . $transaction->getTo() . ':' . $transaction->getAmount());

        return ( $transaction->getSignature() == $signature ) ? true : false;
    }

    /**
     * Add transaction to a list of transactions. The method should perform the following checks before adding
     * a transaction and if the transaction doesn’t pass at least one check, it should
     * be simply ignored without throwing any exceptions:
     * - validate the transaction using a validateTransaction method
     * - check if the number of existing transactions in block is less than 10
     * - check if transaction with transaction.id doesn’t already exist in the list of transactions in this block
     * @param $transaction type: Transaction
     */
    public function addTransaction( $transaction )
    {
        if ( $this->validateTransaction($transaction) && count($this->Transaction) < 10 )
        {
            foreach($this->Transaction as $t)
            {
                if ( $t->getId() == $transaction->getId() ) return; // Transaction already exist in Bloc
            }

            $this->Transaction[] = $transaction;
        }
    }
}

/*
 * Contains the whole block tree
 */
class BlockChain
{
    private $BlockTree = array();   /* (create some custom type to store the tree) – a tree of Block
                                     * objects. Every Block will have only 1 ancestor and the unlimited number of
                                     * descendants. There will be only 1 tree root – the Block without any parents.
                                     */
    public function __construct()
    { }

    public function __destruct()
    {/*
        echo '<pre>';
        var_dump($this);
        echo '</pre>';*/
    }

    /**
     * The method should return a list of Blocks within the longest chain of Blocks in the Block Tree
     */
    public function getBlockChain()
    {
        $max = 0;
        $ChainsBlocksCount = array();

        foreach ($this->BlockTree as $chain => $blocks) {
            $ChainsBlocksCount[$chain] = count($blocks);
            if( max($ChainsBlocksCount) == count($blocks) ) $max = $chain;
        }

        return $this->BlockTree[$max];
    }

    /**
     * Validate passed block. The method should perform the following checks:
     * - the block has at least 1 transaction
     * - the block with the same id doesn’t exist in the "Block Tree" yet
     * The method should return:
     * - true – if the passed block is valid
     * - false – otherwise
     * @param $block type: Block
     * @return boolean
     */
    public function validateBlock( $block )
    {
        if ( count($block->getTransactions() ) < 1 ) return false;

        foreach ($this->BlockTree as $chain => $blocks) {
            foreach ($blocks as $b) {
                if ( $b->getId() == $block->getId() ) return false; // block with the same id exist in the "Block Tree"
            }
        }

        return true;
    }

    /**
     * Add block to the "BlockTree". The method should perform the following checks before adding a
     * block to the tree, and if at least one of checks fails, the method should simply ignore the block
     * without throwing any exceptions:
     * - validate the block using a validateBlock method
     * - parentBlockId is null and the root block already exists. As there can be
     * only one root block, we cannot add more blocks like that.
     * - parentBlockId refers to a block that doesn’t exist in the "Block Tree"
     * - adding "block" to the existing "parentBlockId" block will lead to negative balance on some accounts.
     * @param $parentBlockId type: int
     * @param Block $block
     */
    public function addBlock( $parentBlockId, $block )
    {
        if ( $this->validateBlock($block) ) {
            if ( $parentBlockId == null && count($this->BlockTree ) ) return; // parentBlockId is null and the root block already exists

            if ( $parentBlockId == null ) {
                $this->BlockTree[0][] = $block;
                echo 'Add genesis block: success' . "<br>\n";
                return;
            }

            foreach ($this->BlockTree as $chain => $blocks) {
                echo 'Chain: ' . $chain . "<br>\n";
                foreach ($blocks as $b) {
                    if ( $b->getId() == $parentBlockId )
                    {
                        $this->BlockTree[$chain][] = $block;
                        echo 'Add new block' . "<br>\n";
                    }
                }
            }
            return; // parentBlockId refers to a block that doesn’t exist in the "Block Tree"
        }
    }

    /**
     * Get balance of the "account". The method should perform the following checks:
     * - if the “account” is null, is shorter than 2 characters or longer than 100
     * characters – throw an exception
     * The method should return a calculated account balance, using the longest
     * existing chain of blocks in the tree. All transfers to the account or emissions
     * to the account should increase the balance, and all transfers “from” the
     * account should decrease it. If the account didn’t receive any funds, then return zero
     * @param $account type: string
     * @return integer
     */
    public function getBalance( $account )
    {
        $balance = 0;

        if ( is_null($account) || mb_strlen($account) < 2 || mb_strlen($account) > 100 ) {
            throw new Exception('Error in account: It\'s missing, or is shorter than 2 characters or longer than 100 characters');
        }

        $BlockChain = $this->getBlockChain();

        foreach ( $BlockChain as $b ) {
            foreach ($b->getTransactions() as $t) {
                if ($t->getTo() == $account) {
                    $balance += $t->getAmount();
                }
                if ($t->getFrom() == $account) {
                    $balance -= $t->getAmount();
                }
            }
        }

        return $balance;
    }
}

?>