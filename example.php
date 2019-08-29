<?php

include 'src\testcoin.php';

/**
 * Example use Testcoin
 */

try {
        // create 100 coins and transfer them to Bob
        $trx1 = new Transaction();
        $trx1->setId(1);
        $trx1->setType(Transaction::MONEY_EMISSION);
        $trx1->setTo("bob");
        $trx1->setAmount(100);
        $trx1->setSignature("valid signature goes here");
        $block1 = new Block();
        $block1->setId(1);
        $block1->addTransaction($trx1);

        $blockChain = new BlockChain();
        $blockChain->addBlock(null, $block1);

        // bob transfers 50 coins to alice
        $trx2 = new Transaction();
        $trx2->setId(2);
        $trx2->setType(Transaction::MONEY_TRANSFER);
        $trx2->setFrom("bob");
        $trx2->setTo("alice");
        $trx2->setAmount(50);
        $trx2->setSignature("valid signature goes here");
        $block2 = new Block();
        $block2->setId(2);
        $block2->addTransaction($trx2);

        $blockChain->addBlock(1, $block2);

        echo "<br>";
        echo 'Alice: ' . $blockChain->getBalance("alice") . "<br>\n";   // should return 50
        echo 'Bob: ' . $blockChain->getBalance("bob") . "<br>\n";     // should return 50

} catch (Exception $e) {
    echo 'Error: ',  $e->getMessage(), "\n";
}

?>