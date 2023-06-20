import Chart from 'chart.js/auto'

const pageReport = () => {
    if (window.location.pathname === "/main") {

        console.log('run report page')

        const incomesChart = document.getElementById('incomes')
        const expensivesChart = document.getElementById('expensives')
        const balancesChart = document.getElementById('balances')

        const dataIncome = JSON.parse(incomesChart.getAttribute('data-income'))[0];
        const dataExpensive = JSON.parse(expensivesChart.getAttribute('data-expensive'))[0];
        const dataBalance = JSON.parse(balancesChart.getAttribute('data-balance'))[0];
        
          new Chart(
            incomesChart,
            {
              type: 'pie',
              data: {
                labels: dataIncome.labels,
                datasets: [
                  {
                    label: dataIncome.name,
                    data: dataIncome.values
                  }
                ]
              },
              options: {
                plugins:{
                    legend: {
                        display: false,
                    }
                },
                layout: {
                    padding: 50
                }
              }
            }
          );
          new Chart(
            expensivesChart,
            {
              type: 'pie',
              data: {
                labels: dataExpensive.labels,
                datasets: [
                  {
                    label: dataExpensive.name,
                    data: dataExpensive.values
                  }
                ]
              },
              options: {
                plugins:{
                    legend: {
                        display: false,
                    }
                },
                layout: {
                    padding: 50
                }
              }
            }
          );
          new Chart(
            balancesChart,
            {
              type: 'line',
              data: {
                labels: dataBalance.labels,
                datasets: [
                  {
                    label: dataBalance.name,
                    data: dataBalance.values,
                    fill: true,
                    tension: 0.3
                  }
                ],
              },
              options: {
                plugins:{
                    legend: {
                        display: false,
                    }
                },
                layout: {
                    padding: window.innerWidth >= 1366 ? 50 : 0
                }
              }
            }
          );
    }
}

document.addEventListener("turbo:load", () => {
    pageReport()
})

window.addEventListener('DOMContentLoaded', () => {
    pageReport()
})
