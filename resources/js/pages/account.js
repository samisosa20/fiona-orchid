import Chart from 'chart.js/auto'

const pageReport = () => {
    if (/^\/accounts\/.+\/movements$/.test(window.location.pathname)) {

        console.log('run movement account page')

        const balancesChart = document.getElementById('balances')

        const dataBalance = JSON.parse(balancesChart.getAttribute('data-balance'))[0];
        
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
                  padding: window.innerWidth >= 1366 ? 30 : 0
                },
                aspectRatio: window.innerWidth >= 1366 ? 4 : 2
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
