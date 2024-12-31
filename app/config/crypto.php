<?php
///////////////////////////////////////////////////////////
////    CRYPTO
///////////////////////////////////////////////////////////

/**
 * Gera uma string alfanumérica aleatória
 * 
 * Pode ser a partir de um texto inicial
 * @param int $tam O tamanho desejado da string a ser gerada
 * @param string $txt_ini Texto inicial
 * @return string String aleatória iniciada com $txt_ini
 */

function GeraStr($tam, $txt_ini = null)
{
	$output = $txt_ini;

	for ($i = 0; $i < $tam; $i++) {
		$sorteio = rand(1, 3);		//	sorteia se o char da vez será número, letra maiúscula ou minúscula

		if ($sorteio == 1)
			$nr = rand(48, 57);		//	um número [0-9]
		elseif ($sorteio == 2)
			$nr = rand(65, 90);		//	uma letra maiúscula [A-Z]
		else
			$nr = rand(97, 122);	//	uma letra minúscula [a-z]

		$ch[$i] = chr($nr);
		$output .= $ch[$i];
	}

	return $output;
}

/**
 * Embaralha uma string
 * 
 * @param string $str A string a ser embaralhada
 * @return string A string embaralhada.
 */

function mix(string $str) : string
{														//	$str = "012345678"
	//	tamanho da string
	$tam = strlen($str);								//	$tam = 9

	//	divida a string no meio (se $tam for ímpar a primeira parte fica maior)
	$meio = ceil($tam / 2);								//	$tam1 = 5
	
	/**
	 * Fase 1 do embaralhamento
	 * --
	 * 1 - parte a string no meio;
	 * 2 - escreve cada metade de trás para frente; e
	 * 3 - intercala entre um caracter da primeira e outro da segunda metade.
	 */

	// Obtém as duas metades usando substr
	$str1 = strrev(substr($str, 0, $meio));			//	$str1 = "43210"
	$str2 = strrev(substr($str, $meio));			//	$str2 = "8765"
	
	$str_emb1 = null;			

	// Intercala os caracteres das duas metades
	for ($i = 0; $i < $meio; $i++) {
		if ($i < strlen($str1)) 
			$str_emb1 .= $str1[$i];

		if ($i < strlen($str2)) 
			$str_emb1 .= $str2[$i];
	}												//	$str_emb1 = "483726150"

	/**
	 * Fase 2 do embaralhamento
	 * --
	 * 1 - quebra a string (embaralhada na fase 1) no meio
	 * 2 - escreve cada metade de trás para frente; e
	 * 3 - junta as duas metades.
	 */
	
	// Obtém as duas metades usando substr
	$str1 = strrev(substr($str_emb1, 0, $meio));		//	$str1 = "27384"
	$str2 = strrev(substr($str_emb1, $meio));			//	$str2 = "0516"
	
	$str_emb2 = $str1 . $str2;							//	$str_emb2 = "273840516"

	return $str_emb2;
}

/**
 * Desembaralha uma string embaralhada com mix()
 * 
 * @param string $str_emb A string a ser desembaralhada
 * @param int $qtde A quantidade de vezes que a string foi embaralhada.
 * @return string A string desembaralhada.
 */

function unMix(string $str_emb) : string
{														//	$str_emb = "273840516"
	$tam = strlen($str_emb);							//	$tam = 9
	$meio = ceil($tam / 2);								//	$meio = 5
	
	/**
	 * Fase 1 do desembaralhamento
	 * --
	 * 1 - parte a string no meio;
	 * 2 - escreve cada metade de trás para frente; e
	 * 3 - junta as duas metades.
	 */

	// Obtém as duas metades usando substr
	$str1 = strrev(substr($str_emb, 0, $meio));		//	$str1 = "48372"
	$str2 = strrev(substr($str_emb, $meio));		//	$str2 = "6150"
	
	$str_desemb1 = $str1 . $str2;					//	$str_desemb1 = "483726150"
	
	/**
	 * Fase 2 do desembaralhamento
	 * --
	 * 1 - pega os caracteres das posições pares;
	 * 2 - pega os caracteres das posições ímpares; e
	 * 3 - junta ambos.
	 */

	//	inicialize as duas metades como null
	$str1 = $str2 = null;
	
	for ($i = 0; $i < strlen($str_desemb1); $i++) {
		if ($i == 0 || $i % 2 == 0) 
			$str1 = $str1 . $str_desemb1[$i];		//	$str1 = "43210"
		else
			$str2 = $str2 . $str_desemb1[$i];		//	$str2 = "8765"
	}
	
	$str_desemb2 = strrev($str1) . strrev($str2);	//	$str_desemb2 = "0123456789"

	return $str_desemb2;
}

/**
 * Cifra uma string com criptografia simétrica
 * 
 * @param string $str A string a ser cifrada.
 * @return string A string cifrada.
 */

function Crypto2($str)
{
	//	converte a string em binário
	$str_bin = null;

	for ($i = 0; $i < strlen($str); $i++) {
		$str_bin .= sprintf("%08b", ord($str[$i]));
	}

	//	embaralha str_bin
	$str_c = mix($str);		//	str cifrada

	return $str_c;
}

/**
 * Decifra uma string cifrada com Crypto()
 * 
 * @param string $str A string a ser cifrada.
 * @return string A string cifrada.
 */

function unCrypto2($str)
{
	//	$str é uma sequência de bits embaralhados

	//	desembaralha $str
	$str_d = unMix($str);		//	$str desembaralhada

	//	converta os bits em caracteres
	$string = null;

	for ($i = 0; $i < strlen($str_d); $i += 8) {
		$byte = substr($str_d, $i, 8);
		$string .= chr(bindec($byte));
	}

	return $string;
}



?>
