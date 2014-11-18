var EventSystem = (function() {
  var self = this;

  self.queue = {};

  return {
    fire: function (event, data) {
      var queue = self.queue[event];

      if (typeof queue === 'undefined') {
        return false;
      }

      jQuery.each( queue, function( key, method ) {
        (method)(data);
      });

      return true;
    },
    listen: function(event, callback) {
      if (typeof self.queue[event] === 'undefined') {
        self.queue[event] = [];
      }

      self.queue[event].push(callback);
    }
  };
}());

var TestsTable = React.createClass({
    getInitialState: function() {
        return {data: [], selected: { name: '', id: null}};
    },

    loadFromServer: function() {
        if (this.state.selected.id)
        {
            $.ajax({
                url: this.props.url + this.state.selected.id,
                dataType: 'json',
                success: function(data) {
                    this.setState({data: data});
                }.bind(this),
                error: function(xhr, status, err) {
                    console.error(this.props.url, status, err.toString());
                }.bind(this)
            });
        }
    },

    componentDidMount: function() {
        this.loadFromServer();
        setInterval(this.loadFromServer, this.props.pollInterval);
        EventSystem.listen('selected.changed', this.selectedChanged);
    },

    selectedChanged: function(event) {
        this.setState({selected: event.selected});
    },

    render: function() {
        return (
            <TestList data={this.state.data} />
        );
    }
});

var TestList = React.createClass({
    getInitialState: function() {
        return {data: [], selected: { name: '', id: null}};
    },

    componentDidMount: function() {
        EventSystem.listen('selected.changed', this.selectedChanged);
    },

    selectedChanged: function(event) {
        this.setState({selected: event.selected});
    },

    render: function() {
        var testNodes = this.props.data.map(function (test)
        {
            return (
                <tr key={test.id}>
                    <td>{test.name}</td>
                    <td>{test.updated_at}</td>
                    <td><State type={test.state} /></td>
                </tr>
            );
        });

        return (
            <div>
                <h2>{this.state.selected.name} - Tests</h2>

                <table className="table">
                    <thead>
                        <tr>
                            <th width="70%">Test</th>
                            <th>Last Run</th>
                            <th>State</th>
                        </tr>
                    </thead>

                    <tbody id="#tests-table">
                        {testNodes}
                    </tbody>
                </table>
			</div>
        );
    }
});

var State = React.createClass({
    render: function() {
        var color;

        if (this.props.type == 'running')
        {
           color = 'info';
        }
        else if (this.props.type == 'ok')
        {
           color = 'success';
        }
        else if (this.props.type == 'failed')
        {
           color = 'danger';
        }
        else if (this.props.type == 'queued')
        {
           color = 'default';
        }

        return (
			<span className={"label label-"+color}>{this.props.type}</span>
        );
    }
});

React.render(
    <TestsTable url={"/tests/"} pollInterval={2000}/>,
    document.getElementById('table-container')
);

// ------------------------ Projects

var ProjectsMenu = React.createClass({
    getInitialState: function() {
        return {data: []};
    },

    loadFromServer: function() {
        $.ajax({
            url: this.props.url,
            dataType: 'json',
            success: function(data) {
                this.setState({data: data});
                EventSystem.fire('selected.changed', { selected: { name: data[0].name, id: data[0].id }});
            }.bind(this),
            error: function(xhr, status, err) {
                console.error(this.props.url, status, err.toString());
            }.bind(this)
        });
    },

    componentDidMount: function() {
        this.loadFromServer();
    },

    render: function() {
        return (
            <ProjectsMenuItems data={this.state.data} />
        );
    }
});

var ProjectsMenuItems = React.createClass(
{
    handleClick: function(name, id)
    {
        console.log(name);
        console.log(id);
        EventSystem.fire('selected.changed', { selected: { name: name, id: id }});
    },

    render: function() {
        var nodes = this.props.data.map(function (project)
        {
            return (
                <li key={project.id} onClick={this.handleClick.bind(this, project.name, project.id)}>
                    <a href="#">{project.name}</a>
                </li>
            );
        }.bind(this));

        return (
            <ul className="nav nav-sidebar">
                {nodes}
            </ul>
        );
    },

});

React.render(
    <ProjectsMenu url="/projects"/>,
    document.getElementById('projects')
);
