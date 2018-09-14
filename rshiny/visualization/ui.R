library(shiny)


shinyUI(fluidPage(

  # Application title
  #titlePanel(h1("DISCO2 - VISUALIZATION")),

  navbarPage("Visualization",
    tabPanel("Histogram",
      sidebarLayout(
        sidebarPanel(
          h3("Histogram"),
          selectInput("dataset_hist", label = h5("Choose a Data Set"),
                      choices = c("None")),
          conditionalPanel(condition = "input.dataset_hist != 'None' " ,
            selectInput("histVar", label = h5("Choose a Variable"), 
                        choices = NULL),
            sliderInput("bins", label = h5("Number of Bins"),
                        min = 1, max = 50, value = 30)
          )
        ),
    
        mainPanel(
          htmlOutput("hist_fn"),
          plotOutput("hist")
        )
      )
    ),
    
    tabPanel("Scatterplot",
      sidebarLayout(
        sidebarPanel(
          h3("Scatterplot"),
          selectInput("dataset_plot", label = h5("Choose a Data Set"),
                      choices = c("None")),
          # Display this only if the filename input is not None or empty 
          conditionalPanel(condition = "input.dataset_plot != 'None' " ,
            selectInput("plotXVar", label = h5("Choose X Variable"), 
                        choices = NULL),
            selectInput("plotYVar", label = h5("Choose Y Variable"), 
                        choices = NULL),
            checkboxInput("eckert", h5("Eckert Projection"),value=FALSE)
          )
        ),
        
        mainPanel(
          htmlOutput("scatterplot_fn"),
          plotOutput("scatterplot", hover = "plot_hover"),
          conditionalPanel(condition = "input.dataset_plot != 'None' ",
            verbatimTextOutput("plotInfo")
          )
        )
      )
    ),
    
    tabPanel("View Data",
      sidebarLayout(
        sidebarPanel(
          h3("View Data"),
          selectInput("dataset_view", label = h5("Choose a Data Set"),
                    choices = c("None"))      

        ),
        mainPanel(
          conditionalPanel(condition = "input.dataset_view != 'None' ",
            htmlOutput("viewData_fn"),
            verbatimTextOutput("summary"), 
            DT::dataTableOutput("dataTable")
          )
        )
      )
    )
    
  ) #navbar
  
)) #shinyUI


