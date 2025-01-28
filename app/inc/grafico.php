<?php

/**
 * Obtenção dos dados da META
 */

$meta = getSelect("SELECT *, DATEDIFF(`dia`, CURDATE()) AS dias_ate_meta FROM `v_meta` WHERE {$this_user_v}");

if (! empty($meta)) {
	$meta_dia = $meta[0]["dia"];
	$meta_ts = $meta[0]["ts"];
	$meta_est = $meta[0]["estagio"];
	$meta_folha = $meta[0]["folha"];
	$meta_valor = $meta[0]["valor"];

	$dias_ate_meta = $meta[0]["dias_ate_meta"];

	$valor_atual = sqlResult("SELECT MAX(`valor`) `valor` FROM `v_desempenho` WHERE {$this_user_v}", "valor");
	$diff = $meta_valor - $valor_atual;

	//	folhas por dia para atingir a meta
	$media = number_format($diff / $dias_ate_meta, 1, ',', '');

	//	subtítulo do gráfico de Desempenho
	$subtitle = "{$diff} folhas até a meta! Média de {$media}/dia.";

	$serie_meta = "
		{
			name: 'Meta',
			type: 'scatter', // Define que é um ponto isolado
			data: [
				{
					x: {$meta_ts},
					y: {$meta_valor},
					marker: {
						symbol: 'circle',
						radius: 5,
						fillColor: 'red'
					},
					dataLabels: {
						enabled: true, 
						format: 'META', 
						style: {
							color: 'red',
							fontWeight: 'bold'
						}
					}
				}
			],
			color: 'red',
			tooltip: {
				pointFormatter: function () {
					const estagio = calcularEstagio(this.y);
					const dataFormatada = Highcharts.dateFormat('%d/%b', this.x);
					return `
						Dia: \${dataFormatada}<br>
						Estágio: \${estagio.Atual}<br>
						Folha: \${estagio.Folhas}
					`;
				}
			}
		}
	";
}
else
	$meta_dia = $meta_ts = $meta_est =  $meta_folha =  $meta_valor = $serie_meta = null;

/**
 * Construção das séries definidas em t_series
 */

$result = getSelect("SELECT * FROM `v_series` WHERE {$this_user_v}");

$series = null;

foreach ($result as $linha) {
	$legenda = $linha["legenda"];
	$ts_ini = $linha["ts_ini"];
	$val_ini = $linha["valor_ini"];
	$ts_fim = $linha["ts_fim"];
	$val_fim = $linha["valor_fim"];
	$cor = $linha["cor"];
	$estilo = $linha["estilo"];

	$series .= "
		{
			name: '{$legenda}',
			data: [
				[{$ts_ini}, {$val_ini}],
				[{$ts_fim}, {$val_fim}],
			],
			color: '{$cor}',
			dashStyle: '{$estilo}',
			marker: {
				enabled: false
			}
		},
	";

}

//	FIM DA CONSTRUÇÃO DAS SERIES

/**
 * Construção dos gráficos
 */

//	obtenção dos dados para o gráfico de desempenho

//	apenas o primeiro dia de cada mês
$sql = "(SELECT `dia`, `ts`, `valor`, DATEDIFF('{$meta_dia}' , `dia`) AS dias_ate_meta FROM `v_desempenho` WHERE DATE_FORMAT(`dia`, '%d') = '01' AND {$this_user_v})";

$sql .= "UNION";

//	o último registro (que pode ser qualquer dia)
$sql .= "(SELECT `dia`, `ts`, `valor`, DATEDIFF('{$meta_dia}' , `dia`) AS dias_ate_meta FROM `v_desempenho` WHERE {$this_user_v} ORDER BY `dia` DESC LIMIT 1)";

$result = getSelect($sql);

$dados_desempenho = $dados_media =  $ts_anterior = null;

foreach ($result as $linha) {
	$ts = $linha["ts"];         //  TimeStamp do dia

	/**
	 * o último registro pode coincidir com um dia PRIMEIRO
	 * o IF abaixo evita duplicação
	 */
	if ($ts != $ts_anterior) {
		$dia = $linha["dia"];			//  yyyy-mm-dd
		$valor = $linha["valor"];
	
		$dados_desempenho .= "[$ts, $valor],";

		//	para o g´rafico da média
		$dias_ate_meta = $linha["dias_ate_meta"];

		//	qtse de folhas até a meta
		$diff = $meta_valor - $valor;

		$media = number_format($diff / $dias_ate_meta, 1);

		$dados_media .= "[$ts, $media],";

	}

	$ts_anterior = $ts;
}

//	obtenção dos dados para o gráfico dias com ou sem execução
$sql = "SELECT * FROM `v_desempenho` WHERE `dia` >= CURDATE() - INTERVAL 12 MONTH AND {$this_user_v}";

$result = getSelect($sql);

$meses_arr = [];         // Array para armazenar os meses formatados
$qt_zeros_arr = [];        // Array para contar os zeros por mês
$qt_nzeros_arr = [];     // Array para contar os não-zeros por mês

foreach ($result as $linha) {
	// Extrai o mês e ano do campo 'dia'
	$dia = strtotime($linha['dia']); // Converte a data para timestamp
	$mesAno = date("M/y", $dia);     // Formata como "Abr/24"

	$meses_ingles = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
	$meses_port = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];

	$mesAno = str_replace($meses_ingles, $meses_port, $mesAno);

	// Inicializa os contadores para o mês caso ainda não existam
	if (!isset($qt_zeros_arr[$mesAno])) {
		$qt_zeros_arr[$mesAno] = 0;
		$qt_nzeros_arr[$mesAno] = 0;
		$meses_arr[] = $mesAno; // Adiciona o mês na ordem que aparece
	}

	// Incrementa os contadores com base no valor de 'qtde'
	if ($linha['qtde'] == 0) {
		$qt_zeros_arr[$mesAno]++;
	} else {
		$qt_nzeros_arr[$mesAno]++;
	}
}

// Gera as strings no formato desejado
$meses = "'" . implode("','", $meses_arr) . "'"; // Meses formatados
$qt_zeros = implode(", ", array_map(fn($mes) => $qt_zeros_arr[$mes], $meses_arr)); // Quantidade de zeros
$qt_nzeros = implode(", ", array_map(fn($mes) => $qt_nzeros_arr[$mes], $meses_arr)); // Quantidade de não-zeros

// Converte o array em uma string no formato desejado
$meses = "'" . implode("','", $meses_arr) . "'";

// FIM

//	obtenção dos dados para o gráfico da quantidade de blocos por mês
$sql = "SELECT DATE_FORMAT(`dia`, '%b/%y') AS mes, SUM(`qtde`) AS total_qtde FROM `v_desempenho` WHERE `dia` >= CURDATE() - INTERVAL 12 MONTH AND {$this_user_v} GROUP BY `mes`";

$result = getSelect($sql);

//$meses_arr = [];         // Array para armazenar os meses formatados
$qt_folhas_arr = [];        // Array para contar os zeros por mês

foreach ($result as $linha) {
	$qt_folhas_arr[] = $linha['total_qtde'];
}

// Gera as strings no formato desejado
//$meses = "'" . implode("','", $meses_arr) . "'"; // Meses formatados
$qt_folhas = implode(', ', $qt_folhas_arr);


echo "
	<div class='swiper'>
		<div class='swiper-wrapper'>
			<div class='swiper-slide'>
				<div id='chart1'></div>
			</div>
			<div class='swiper-slide'>
				<div id='chart2'></div>
			</div>
			<div class='swiper-slide'>
				<div id='chart3'></div>
			</div>
			<div class='swiper-slide'>
				<div id='chart4'></div>
			</div>
		</div>
	</div>";


$bot_home = "<button class='btn btn-primary mt-2 hide-on-landscape'><i class='fa-solid fa-house'></i> &nbsp;Voltar</button>";

$bot_meta = "<button class='btn btn-primary mt-2 hide-on-landscape' name='page' value='meta'><i class='fa-solid fa-pen-to-square'></i> &nbsp;Alterar meta</button>";

echo "<div class='text-center'><form method='POST' action=''>{$bot_home} &ensp; {$bot_meta}</form></div>";

?>

<script language='javascript'>

const estagios = [
	"A1", "A2", "B1", "B2", "C1", "C2", "D1", "D2", "E1", "E2", 
	"F1", "F2", "G1", "G2", "H1", "H2", "I1", "I2", "J1", "J2",
	"K1", "K2", "L1", "L2", "M1", "M2", "N1", "N2", "O1", "O2", 
	"P1", "P2", "Q1", "Q2", "R1", "R2", "S1", "S2", "T1", "T2",
	"U1", "U2", "V1", "V2", "W1", "W2", "X1", "X2", "Y1", "Y2", 
	"Z1", "Z2"
];

function calcularEstagio(folhas) {
	const folhasPorEstagio = 200; // Cada estágio tem 200 folhas

	const totalEstagios = estagios.length;
	const folhasConcluidas = Math.floor(folhas / folhasPorEstagio); // Quantos estágios completos
	const Folhas = folhas % folhasPorEstagio; // Folhas no estágio atual

	const Concluido = folhasConcluidas > 0 && folhasConcluidas <= totalEstagios
		? estagios[folhasConcluidas - 1]
		: null;

	const Atual = folhasConcluidas < totalEstagios
		? estagios[folhasConcluidas]
		: "Finalizado"; // Considera que não há mais estágios após o último

	return {
		Concluido: Concluido || "Nenhum",
		Atual,
		Folhas
	};
}

Highcharts.setOptions({
	lang: {
		months: [
			'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
			'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
		],
		weekdays: [
			'Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 
			'Quinta-feira', 'Sexta-feira', 'Sábado'
		],
		shortMonths: [
			'jan', 'fev', 'mar', 'abr', 'mai', 'jun', 
			'jul', 'ago', 'set', 'out', 'nov', 'dez'
		],
		decimalPoint: ',',
		thousandsSep: '.'
	}
});

const chart1 = Highcharts.chart('chart1', {
	credits: {
		enabled: false // Desativa os créditos do Highcharts
	},
	chart: {
		type: 'line'  // Gráfico de linha
	},
	title: {
		text: 'Em busca da meta'
	},
	subtitle: {
		text: '<?php echo $subtitle;?>'
	},
	xAxis: {
		title: {
			text: 'Meses'  // Título do eixo X
		},
		type: 'datetime',
		tickInterval: 30 * 24 * 3600 * 1000,  // Intervalo de 1 mês em milissegundos
		labels: {
			formatter: function() {
				// Formatação da data para exibir mês e ano
				return Highcharts.dateFormat('%b/%y', this.value);  // Exemplo: "set/24"
			},
			x: 0,  // Alinhamento horizontal do texto (0 = centralizado)
			style: {
				textAlign: 'center'  // Garante que o texto esteja centralizado
			}
		},
		// min: Date.UTC(2024, 8, 1),  // Data mínima (01/09/2024)
		//max: Date.UTC(2025, 5, 30), // Data máxima (30/06/2025)
		gridLineWidth: 1,  // Linha de grade visível
		gridLineDashStyle: 'Dash', // Estilo da linha de grade (opcional)
	},
	yAxis: {
		title: {
			text: 'Estágio'  // Título do eixo Y
		},
		tickInterval: 200, // Define o intervalo fixo no eixo Y
		//min: 0 // Opcional: Define o valor mínimo no eixo Y
		labels: {
			formatter: function () {
				const index = Math.floor(this.value / 200);
				return estagios[index] || '?'; // Retorna o estágio correspondente ou vazio se o valor exceder a lista
			},
			style: {
				fontFamily: 'monospace', // Define a fonte como monoespaçada
				fontSize: '10px'        // Define o tamanho da fonte (opcional)
			}
		}
	},
	tooltip: {
		shared: true,
		crosshairs: true,
		valueSuffix: ' folhas'
	},
	series: [
		<?php echo $series;?>
		//	Série do desepenho 
		{
			name: 'Desempenho',
			data: [
				<?php echo $dados_desempenho; ?>,  // Dados vindo do PHP
			],
			color: '#f45b5b',
			tooltip: {
				headerFormat: '', // Remove o cabeçalho padrão
				pointFormatter: function () {
					const estagio = calcularEstagio(this.y);
					const dataFormatada = Highcharts.dateFormat('%d/%b', this.x);
					return `
						<b>${this.series.name}</b><br>
						Dia: ${dataFormatada}<br>
						Estágio: ${estagio.Atual}<br>
						Folhas: ${estagio.Folhas}
					`;
				}
			}
		},
		<?php echo $serie_meta;?>,
	]
});

const chart2 = Highcharts.chart('chart2', {
	credits: {
		enabled: false // Desativa os créditos do Highcharts
	},
	chart: {
		type: 'column'
	},
	title: {
		text: 'Execução diária'
	},
	subtitle: {
		text: 'Dias com ou sem execução de atividades'
	},
	xAxis: {
		categories: [<?php echo $meses;?>]
	},
	yAxis: {
		min: 0,
		title: {
			text: 'Dias'
		},
		stackLabels: {
			enabled: true
		}
	},
	legend: {
		align: 'left',
		x: 70,
		verticalAlign: 'top',
		y: 70,
		floating: true,
		backgroundColor:
			Highcharts.defaultOptions.legend.backgroundColor || 'white',
		borderColor: '#CCC',
		borderWidth: 1,
		shadow: false
	},
	tooltip: {
		headerFormat: '<b>{category}</b><br/>',
		pointFormat: '{series.name}: {point.y}<br/>dias de {point.stackTotal}'
	},
	plotOptions: {
		column: {
			stacking: 'normal',
			dataLabels: {
				enabled: true
			}
		}
	},
	series: [{
		name: 'Não fez',
		data: [<?php echo $qt_zeros;?>],
		color: '#ef6e88'
	}, {
		name: 'Fez',
		data: [<?php echo $qt_nzeros;?>],
		color: '#82e598'
	}]
});

const chart3 = Highcharts.chart('chart3', {
	credits: {
		enabled: false // Desativa os créditos do Highcharts
	},
	chart: {
		type: 'column'
	},
	title: {
		text: 'Execução mensal'
	},
	subtitle: {
		text: 'Quantidade de folhas por mês'
	},
	xAxis: {
		categories: [<?php echo $meses;?>]
	},
	yAxis: {
		min: 0,
		title: {
			text: 'Dias'
		},
		stackLabels: {
			enabled: true
		}
	},
	legend: {
		align: 'left',
		x: 70,
		verticalAlign: 'top',
		y: 70,
		floating: true,
		backgroundColor:
			Highcharts.defaultOptions.legend.backgroundColor || 'white',
		borderColor: '#CCC',
		borderWidth: 1,
		shadow: false
	},
	tooltip: {
		formatter: function () {
			const blocos = (this.point.stackTotal / 10).toFixed(1); // Divide por 10 e formata com uma casa decimal
			return `
				<b>${this.key}</b><br/> 
				${this.series.name}: ${this.point.y}<br/>
				(${blocos} blocos)
			`;
		}
	},
	plotOptions: {
		column: {
			stacking: 'normal',
			dataLabels: {
				enabled: true
			}
		}
	},
	series: [{
		name: 'Folhas',
		data: [<?php echo $qt_folhas;?>],
		//color: '#ffae00'
	}]
});

const chart4 = Highcharts.chart('chart4', {
	credits: {
		enabled: false // Desativa os créditos do Highcharts
	},
	chart: {
		type: 'line'  // Gráfico de linha
	},
	title: {
		text: 'Distância da meta'
	},
	subtitle: {
		text: 'Variação da média de blocos por dia'
	},
	xAxis: {
		title: {
			text: 'Dia'  // Título do eixo X
		},
		type: 'datetime',
		tickInterval: 30 * 24 * 3600 * 1000,  // Intervalo de 1 mês em milissegundos
		labels: {
			formatter: function() {
				// Formatação da data para exibir mês e ano
				return Highcharts.dateFormat('%d/%b', this.value);  // Exemplo: "set/24"
			},
			x: 0,  // Alinhamento horizontal do texto (0 = centralizado)
			style: {
				textAlign: 'center'  // Garante que o texto esteja centralizado
			}
		},
		gridLineWidth: 1,  // Linha de grade visível
		gridLineDashStyle: 'Dash', // Estilo da linha de grade (opcional)
	},
	yAxis: {
		title: {
			text: 'Média'  // Título do eixo Y
		},
		tickInterval: 0.1, // Define o intervalo fixo no eixo Y
		labels: {
            formatter: function () {
                return this.value.toFixed(1); // Força 1 casa decimal
            }
        },
	},
	tooltip: {
		shared: true,
		crosshairs: true,
		valueSuffix: ' folhas'
	},
	series: [
		//	Série da média 
		{
			name: 'Média para a meta',
			data: [
				<?php echo $dados_media; ?>,  // Dados vindo do PHP
			],
			color: '#f45b5b',
			tooltip: {
				headerFormat: '', // Remove o cabeçalho padrão
				pointFormatter: function () {
					const media = this.y;
					const dataFormatada = Highcharts.dateFormat('%d/%b', this.x);
					return `
						<b>${this.series.name}</b><br>
						Em: ${dataFormatada}<br>
						${this.y} folhas/dia.
					`;
				}
			}
		},
	]
});
	
$(document).ready(function() {
	//	Adicionar a classe aos elementos (para ocultação ao virar a tela)
	$('#titulo, #identificacao').addClass('hide-on-landscape');

	//	Para o slidegraph

	//	Inicializa o Swiper (slide)
	 const swiper = new Swiper('.swiper', {
	 	direction: 'horizontal',	// Navegação horizontal
	 	loop: false,				// Não repetir o carrossel
	 });
});

</script>